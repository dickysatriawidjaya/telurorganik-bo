<template>
  <div class="app-container">
    <el-table v-loading="loading" :data="list" border fit highlight-current-row style="width: 100%">
      <el-table-column align="center" label="ID" width="80">
        <template slot-scope="scope">
          <span>{{ scope.row.index }}</span>
        </template>
      </el-table-column>

      <el-table-column width="150" align="center" :label="$t('table.name')">
        <template slot-scope="scope">
          <span>{{ scope.row.name | uppercaseFirst }}</span>
        </template>
      </el-table-column>

      <el-table-column align="left" :label="$t('table.description')">
        <template slot-scope="scope">
          <span>{{ scope.row.description }}</span>
        </template>
      </el-table-column>

      <el-table-column v-if="checkPermission(['manage permission'])" align="center" label="Actions" width="100">
        <template slot-scope="scope">
          <el-button v-if="scope.row.name !== 'admin'" v-permission="['manage permission']" size="small" icon="el-icon-edit" circle @click="handleEditPermissions(scope.row.id);" />
        </template>
      </el-table-column>
    </el-table>

    <el-dialog :visible.sync="dialogVisible" :title="'Edit Permissions - ' + currentRole.name">
      <div v-loading="dialogLoading" class="form-container">
        <div class="permissions-container">
          <div class="block">
            <el-form :model="currentRole" label-width="80px" label-position="top">
              <el-form-item label="Menus">
                <el-tree ref="menuPermissions" :data="menuPermissions" :default-checked-keys="permissionKeys(roleMenuPermissions)" :props="permissionProps" show-checkbox node-key="id" class="permission-tree" />
              </el-form-item>
            </el-form>
          </div>
          <div class="block">
            <el-form :model="currentRole" label-width="80px" label-position="top">
              <el-form-item label="Permissions">
                <el-tree ref="otherPermissions" :data="otherPermissions" :default-checked-keys="permissionKeys(roleOtherPermissions)" :props="permissionProps" show-checkbox node-key="id" class="permission-tree" />
              </el-form-item>
            </el-form>
          </div>
          <div class="clear-left" />
        </div>
        <div style="text-align:right;">
          <el-button type="canceltransaksi" @click="dialogVisible=false">
            {{ $t('permission.cancel') }}
          </el-button>
          <el-button type="addtransaksi" @click="confirmPermission">
            {{ $t('permission.confirm') }}
          </el-button>
        </div>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import Resource from '@/api/resource';
import RoleResource from '@/api/role';
import waves from '@/directive/waves'; // Waves directive
import permission from '@/directive/permission'; // Permission directive (v-permission)
import checkPermission from '@/utils/permission'; // Permission checking

const roleResource = new RoleResource();
const permissionResource = new Resource('permissions');

export default {
  name: 'RoleList',
  directives: { waves, permission },
  data() {
    return {
      currentRoleId: 1,
      list: [],
      loading: true,
      dialogLoading: false,
      dialogVisible: false,
      permissions: [],
      menuPermissions: [],
      otherPermissions: [],
      permissionProps: {
        children: 'children',
        label: 'name',
        disabled: 'disabled',
      },
    };
  },
  computed: {
    currentRole() {
      const found = this.list.find(role => role.id === this.currentRoleId);
      if (found === undefined) {
        return { name: '', permissions: [] };
      }

      return found;
    },
    rolePermissions() {
      return this.classifyPermissions(this.currentRole.permissions).all;
    },
    roleMenuPermissions() {
      return this.classifyPermissions(this.currentRole.permissions).menu;
    },
    roleOtherPermissions() {
      return this.classifyPermissions(this.currentRole.permissions).other;
    },
  },
  created() {
    this.getRoles();
    this.getPermissions();
  },

  methods: {
    checkPermission,
    async getRoles() {
      this.loading = true;
      const { data } = await roleResource.list({});
      this.list = data;
      this.list.forEach((role, index) => {
        role['index'] = index + 1;
        role['description'] = this.$t('roles.description.' + role.name);
      });
      this.loading = false;
    },

    async getPermissions() {
      const { data } = await permissionResource.list({});
      const { all, menu, other } = this.classifyPermissions(data);
      this.permissions = all;
      this.menuPermissions = menu;
      this.otherPermissions = other;
    },

    classifyPermissions(permissions) {
      const all = []; const menu = []; const other = [];
      permissions.forEach(permission => {
        const permissionName = permission.name;
        all.push(permission);
        if (permissionName.startsWith('view menu')) {
          menu.push(this.normalizeMenuPermission(permission));
        } else {
          other.push(this.normalizePermission(permission));
        }
      });
      return { all, menu, other };
    },

    normalizeMenuPermission(permission) {
      return { id: permission.id, name: this.$options.filters.uppercaseFirst(permission.name.substring(10)) };
    },

    normalizePermission(permission) {
      return { id: permission.id, name: this.$options.filters.uppercaseFirst(permission.name), disabled: permission.name === 'manage permission' };
    },

    permissionKeys(permissions) {
      return permissions.map(permssion => permssion.id);
    },

    handleEditPermissions(id) {
      this.dialogVisible = true;
      this.currentRoleId = id;
      this.$nextTick(() => {
        this.$refs.menuPermissions.setCheckedKeys(this.permissionKeys(this.roleMenuPermissions));
        this.$refs.otherPermissions.setCheckedKeys(this.permissionKeys(this.roleOtherPermissions));
      });
    },

    confirmPermission() {
      const checkedMenu = this.$refs.menuPermissions.getCheckedKeys();
      const checkedOther = this.$refs.otherPermissions.getCheckedKeys();
      const checkedPermissions = checkedMenu.concat(checkedOther);
      this.dialogLoading = true;

      roleResource.update(this.currentRole.id, { permissions: checkedPermissions }).then(response => {
        this.$message({
          message: 'Permissions has been updated successfully',
          type: 'success',
          duration: 5 * 1000,
        });
        this.dialogLoading = false;
        this.dialogVisible = false;
        this.getRoles();
      });
    },
  },
};
</script>

<style lang="scss" scoped>
  .el-dialog{
    width:410px;
    border-radius:10px;
    .el-dialog__header{
      margin: 26px 30px 0px;
      border-bottom: 1px solid #D3D3D3;
      padding: 24px 0 5.5px 0;
      .el-dialog__title {
        color:#707070;
        font-size:27px;
        font-family: 'Abel', sans-serif;
        font-weight:400;
        line-height:43px;
      }
    }
    .el-dialog__body {
      padding: 21px 30px;
      color: #606266;
      font-size: 14px;
      word-break: break-all;
      .form-container{
        .el-form-item--medium .el-form-item__label {
          color:#707070;
          font-size:10px;
          font-family: 'Ubuntu', sans-serif;
          font-weight:500;
          line-height:11px;
          margin-top: 13px;
        }
        .el-input--medium .el-input__inner {
            height: 26px;
            line-height: 26px;
            font-size: 10px;
        }
        .div_tabel{
          .transaksi_tabel_add{
            width:687px;
            border-spacing: 10px;
            border-collapse: separate;
             thead{
              color:#707070;
              font-size:10px;
              font-family: 'Ubuntu', sans-serif;
              font-weight:400;
              line-height:11px;
              text-align:left;
            }
            tbody{
             color:#707070;
             font-size:10px;
             font-family: 'Ubuntu', sans-serif;
             font-weight:400;
             line-height:11px;
             .el-select-dropdown{
               .el-scrollbar{
                 .el-select-dropdown__wrap{
                   .el-select-dropdown__item {
                        font-size: 10px;
                        padding: 0 15px;
                        position: relative;
                        white-space: nowrap;
                        overflow: hidden;
                        text-overflow: ellipsis;
                        color: #606266;
                        height: 20px;
                        line-height: 20px;
                        box-sizing: border-box;
                        cursor: pointer;
                    }
                 }
               }

             }

           }
          }
        }
        .el-select__caret {
            color: #C0C4CC;
            font-size: 14px;
            transition: transform .3s;
            transform: rotateZ(180deg);
            cursor: pointer;
            margin: 3px;
        }
        .el-select-dropdown{
          .el-select-dropdown__list {
              list-style: none;
              padding: 0px 0;
              margin: 0;
              box-sizing: border-box;
          }
          .el-select-dropdown__item {
               font-size: 10px;
               padding: 0 15px;
               position: relative;
               white-space: nowrap;
               overflow: hidden;
               text-overflow: ellipsis;
               color: #606266;
               height: 20px;
               line-height: 20px;
               box-sizing: border-box;
               cursor: pointer;
           }
        }
      }
      .total_price{
        color:#707070;
        font-size:13px;
        font-family: 'Ubuntu', sans-serif;
        font-weight:500;
        line-height:14px;
        text-align:left;
      }
      .dialog-footer{
        text-align:center;
        padding-top: 30px;
        margin-top: 1px;
        margin-left: 0px;
      }
    }
  }
.permissions-container {
  flex: 1;
  justify-content: space-between;
  width:96%;
  font-size: 10px;
  padding: 20px;
  margin:20px;
  background:#FFFFFF;
  border-radius:10px;
  .filter-container{
    .tabel_filter{
      color:#707070;
      font-size:10px;
      font-family: 'Ubuntu', sans-serif;
      font-weight:300;
      line-height:11px;
      .el-input--medium{
        .el-input__inner {
          height: 36px ;
          line-height: 36px;
          font-size:10px;
        }
      }
    }
    .el-select.tabel_filter{
      color:#707070;
      font-size:10px;
      font-family: 'Ubuntu', sans-serif;
      font-weight:300;
      line-height:11px;
      .el-input{
        height: 36px;
        line-height: 36px;
        .el-select__caret {
            color: #C0C4CC;
            font-size: 14px;
            transition: transform .3s;
            /* transform: rotateZ(180deg); */
            cursor: pointer;
            margin: 3px;
        }
        .el-input__inner {
          height: 36px;
          line-height: 36px;
          font-size:10px;
        }
      }
    }
  }
  .el-table{
      color: #707070;
      font-size:10px;
      font-family: 'Ubuntu', sans-serif;
      font-weight: 400;
      line-height:11px;
      padding:0px !important;
      .el-tag {
          padding: 0 5px;
          line-height: 30px;
          font-family: 'Ubuntu', sans-serif;
          font-size: 10px;
      }
  }
  .block {
    float: left;
    min-width: 250px;
  }
  .clear-left {
    clear: left;
  }
  .el-button--medium.is-circle {
    padding: 8px;
  }
  .el-button--addtable {
    color: #707070;
    font-size: 10px;
    font-family: 'Ubuntu', sans-serif;
    font-weight: 500;
    line-height: 11px;
    background-color: transparent;
    border-color: #707070;
    border-radius:13px;
    padding: 7px 20px;
    margin-top: 3px;
  }
  .el-button--add {
    color: #707070;
    font-size: 10px;
    font-family: 'Ubuntu', sans-serif;
    font-weight: 500;
    line-height: 11px;
    background-color: transparent;
    border-color: #707070;
    border-radius:13px;
    float:right;
  }
  .el-button--expexcel {
    color: #FFFFFF;
    font-size: 10px;
    font-family: 'Ubuntu', sans-serif;
    font-weight: 500;
    line-height: 11px;
    background-color: #85E67F;
    border-radius:13px;
  }
  .el-button--canceltransaksi {
    color: #B2B2B2;
    font-size: 13px;
    font-family: 'Ubuntu', sans-serif;
    font-weight: 300;
    line-height: 14px;
    background-color: #F4F4F4;
    border-radius:5px;
    padding:8px 33px;
  }
  .el-button--addtransaksi {
    color: #FFFFFF;
    font-size: 13px;
    font-family: 'Ubuntu', sans-serif;
    font-weight: 500;
    line-height: 14px;
    background-color: #46A2FD;
    border-radius:5px;
    padding:8px 33px;
  }
}
</style>
