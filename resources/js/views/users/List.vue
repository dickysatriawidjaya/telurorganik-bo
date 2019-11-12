<template>
  <div class="app-container">
    <div class="filter-container">
      <el-input v-model="query.keyword" :placeholder="$t('table.keyword')" style="width: 200px;" class="filter-item" @input="handleFilter" />
      <el-select v-model="query.role" :placeholder="$t('table.role')" clearable style="width: 90px" class="filter-item" @change="handleFilter">
        <el-option v-for="item in roles" :key="item" :label="item | uppercaseFirst" :value="item" />
      </el-select>
      <!-- <el-button v-waves class="filter-item" type="primary" icon="el-icon-search" @click="handleFilter">
        {{ $t('table.search') }}
      </el-button> -->
      <el-button class="filter-item" style="margin-left: 10px;" type="add" icon="el-icon-plus" @click="handleCreate">
        {{ $t('table.add') }} User
      </el-button>
    </div>

    <el-table v-loading="loading" :data="list" border fit highlight-current-row style="width: 100%">
      <el-table-column align="center" label="ID" sortable="custom" width="80">
        <template slot-scope="scope">
          <span>{{ scope.row.index }}</span>
        </template>
      </el-table-column>

      <el-table-column align="center" label="Name">
        <template slot-scope="scope">
          <span>{{ scope.row.name }}</span>
        </template>
      </el-table-column>

      <el-table-column align="center" label="Email">
        <template slot-scope="scope">
          <span>{{ scope.row.email }}</span>
        </template>
      </el-table-column>

      <el-table-column align="center" label="Role" width="120">
        <template slot-scope="scope">
          <span>{{ scope.row.roles.join(', ') }}</span>
        </template>
      </el-table-column>

      <el-table-column align="center" label="Actions" width="150">
        <template slot-scope="scope">
          <!-- <router-link v-if="!scope.row.roles.includes('admin')" :to="'/administrator/users/edit/'+scope.row.id"> -->
          <el-button v-if="login_as_role=='admin'" v-permission="['manage user']" size="small" icon="el-icon-edit" circle @click="handleUpdate(scope.row)" />
          <!-- </router-link> -->
          <!-- <el-button v-if="!scope.row.roles.includes('admin')" v-permission="['manage permission']"  size="small" icon="el-icon-edit" @click="handleEditPermissions(scope.row.id);" circle>
          </el-button> -->
          <el-button v-if="!scope.row.roles.includes('admin')" v-permission="['manage user']" size="small" icon="el-icon-delete" circle @click="handleDelete(scope.row.id, scope.row.name);" />
        </template>
      </el-table-column>
    </el-table>

    <pagination v-show="total>0" :total="total" :page.sync="query.page" :limit.sync="query.limit" @pagination="getList" />

    <el-dialog :visible.sync="dialogPermissionVisible" :title="'Edit Permissions - ' + currentUser.name">
      <div v-if="currentUser.name" v-loading="dialogPermissionLoading" class="form-container">
        <div class="permissions-container">
          <div class="block">
            <el-form :model="currentUser" label-width="80px" label-position="top">
              <el-form-item label="Menus">
                <el-tree ref="menuPermissions" :data="normalizedMenuPermissions" :default-checked-keys="permissionKeys(userMenuPermissions)" :props="permissionProps" show-checkbox node-key="id" class="permission-tree" />
              </el-form-item>
            </el-form>
          </div>
          <div class="block">
            <el-form :model="currentUser" label-width="80px" label-position="top">
              <el-form-item label="Permissions">
                <el-tree ref="otherPermissions" :data="normalizedOtherPermissions" :default-checked-keys="permissionKeys(userOtherPermissions)" :props="permissionProps" show-checkbox node-key="id" class="permission-tree" />
              </el-form-item>
            </el-form>
          </div>
          <div class="clear-left" />
        </div>
        <div style="text-align:right;">
          <el-button type="canceltransaksi" @click="dialogPermissionVisible=false">
            {{ $t('permission.cancel') }}
          </el-button>
          <el-button type="addtransaksi" @click="confirmPermission">
            {{ $t('permission.confirm') }}
          </el-button>
        </div>
      </div>
    </el-dialog>

    <el-dialog :title="titleForm" :visible.sync="dialogFormVisible" class="b" :before-close="handleClose">
      <div v-loading="userCreating" class="form-container">
        <el-form ref="userForm" :rules="rules" :model="newUser" label-position="left" label-width="180px" style="max-width: 500px;">
          <el-form-item :label="$t('user.role')" prop="role">
            <el-select v-model="newUser.role" class="filter-item" placeholder="Please select role" style="width:100%;">
              <el-option v-for="item in roles" :key="item" :label="item | uppercaseFirst" :value="item" />
            </el-select>
          </el-form-item>
          <el-form-item :label="$t('user.name')" prop="name">
            <el-input v-model="newUser.name" />
          </el-form-item>
          <el-form-item :label="$t('user.email')" prop="email">
            <el-input v-model="newUser.email" />
          </el-form-item>
          <el-form-item :label="$t('user.password')" prop="password">
            <el-input v-model="newUser.password" show-password />
          </el-form-item>
          <el-form-item :label="$t('user.confirmPassword')" prop="confirmPassword">
            <el-input v-model="newUser.confirmPassword" show-password />
          </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
          <el-button type="canceltransaksi" @click="dialogFormVisible = false">
            {{ $t('table.cancel') }}
          </el-button>
          <el-button type="addtransaksi" @click="createUser()">
            {{ $t('table.confirm') }}
          </el-button>
        </div>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import Cookies from 'js-cookie';
import Pagination from '@/components/Pagination'; // Secondary package based on el-pagination
import UserResource from '@/api/user';
import Resource from '@/api/resource';
import waves from '@/directive/waves'; // Waves directive
import permission from '@/directive/permission'; // Waves directive
import checkPermission from '@/utils/permission'; // Permission checking

const userResource = new UserResource();
const permissionResource = new Resource('permissions');

export default {
  name: 'UserList',
  components: { Pagination },
  directives: { waves, permission },
  data() {
    var validateConfirmPassword = (rule, value, callback) => {
      if (value !== this.newUser.password) {
        callback(new Error('Password is mismatched!'));
      } else {
        callback();
      }
    };
    return {
      titleForm: 'Create User',
      login_as_role: Cookies.get('Role'),
      list: null,
      total: 0,
      loading: true,
      downloading: false,
      userCreating: false,
      query: {
        page: 1,
        limit: 15,
        keyword: '',
        role: '',
      },
      // roles: ['admin', 'manager', 'editor', 'user', 'visitor'],
      roles: ['admin', 'staff'],
      nonAdminRoles: ['editor', 'user', 'visitor'],
      newUser: {},
      dialogFormVisible: false,
      dialogPermissionVisible: false,
      dialogPermissionLoading: false,
      currentUserId: 0,
      currentUser: {
        name: '',
        permissions: [],
        rolePermissions: [],
      },
      rules: {
        role: [{ required: true, message: 'Role is required', trigger: 'change' }],
        name: [{ required: true, message: 'Name is required', trigger: 'blur' }],
        email: [
          { required: true, message: 'Email is required', trigger: 'blur' },
          { type: 'email', message: 'Please input correct email address', trigger: ['blur', 'change'] },
        ],
        password: [{ required: true, message: 'Password is required', trigger: 'blur' }],
        confirmPassword: [{ validator: validateConfirmPassword, trigger: 'blur' }],
      },
      permissionProps: {
        children: 'children',
        label: 'name',
        disabled: 'disabled',
      },
      permissions: [],
      menuPermissions: [],
      otherPermissions: [],
    };
  },
  computed: {
    normalizedMenuPermissions() {
      let tmp = [];
      this.currentUser.permissions.role.forEach(permission => {
        tmp.push({
          id: permission.id,
          name: permission.name,
          disabled: true,
        });
      });
      const rolePermissions = {
        id: -1, // Just a faked ID
        name: 'Inherited from role',
        disabled: true,
        children: this.classifyPermissions(tmp).menu,
      };

      tmp = this.menuPermissions.filter(permission => !this.currentUser.permissions.role.find(p => p.id === permission.id));
      const userPermissions = {
        id: 0, // Faked ID
        name: 'Extra menus',
        children: tmp,
        disabled: tmp.length === 0,
      };

      return [rolePermissions, userPermissions];
    },
    normalizedOtherPermissions() {
      let tmp = [];
      this.currentUser.permissions.role.forEach(permission => {
        tmp.push({
          id: permission.id,
          name: permission.name,
          disabled: true,
        });
      });
      const rolePermissions = {
        id: -1,
        name: 'Inherited from role',
        disabled: true,
        children: this.classifyPermissions(tmp).other,
      };

      tmp = this.otherPermissions.filter(permission => !this.currentUser.permissions.role.find(p => p.id === permission.id));
      const userPermissions = {
        id: 0,
        name: 'Extra permissions',
        children: tmp,
        disabled: tmp.length === 0,
      };

      return [rolePermissions, userPermissions];
    },
    userMenuPermissions() {
      return this.classifyPermissions(this.userPermissions).menu;
    },
    userOtherPermissions() {
      return this.classifyPermissions(this.userPermissions).other;
    },
    userPermissions() {
      return this.currentUser.permissions.role.concat(this.currentUser.permissions.user);
    },
  },
  created() {
    this.resetNewUser();
    this.getList();
    if (checkPermission(['manage permission'])) {
      this.getPermissions();
    }
  },
  methods: {
    handleClose(done) {
      this.$confirm('Are you sure to close this dialog?')
        .then(_ => {
          done();
        })
        .catch(_ => {});
    },
    handleUpdate(data){
      console.log(data);
      this.dialogFormVisible = true;
      this.$nextTick(() => {
        this.$refs['userForm'].clearValidate();
      });
      this.titleForm = 'Edit User';
      this.newUser.role = data.roles;
      this.newUser.name = data.name;
      this.newUser.email = data.email;
    },
    checkPermission,
    async getPermissions() {
      const { data } = await permissionResource.list({});
      const { all, menu, other } = this.classifyPermissions(data);
      this.permissions = all;
      this.menuPermissions = menu;
      this.otherPermissions = other;
    },

    async getList() {
      const { limit, page } = this.query;
      this.loading = true;
      const { data, meta } = await userResource.list(this.query);
      this.list = data;
      this.list.forEach((element, index) => {
        element['index'] = (page - 1) * limit + index + 1;
      });
      this.total = meta.total;
      this.loading = false;
    },
    handleFilter() {
      this.query.page = 1;
      this.getList();
    },
    handleCreate() {
      this.resetNewUser();
      this.dialogFormVisible = true;
      this.$nextTick(() => {
        this.$refs['userForm'].clearValidate();
      });
    },
    handleDelete(id, name) {
      this.$confirm('This will permanently delete user ' + name + '. Continue?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning',
      }).then(() => {
        userResource.destroy(id).then(response => {
          this.$message({
            type: 'success',
            message: 'Delete completed',
          });
          this.handleFilter();
        }).catch(error => {
          console.log(error);
        });
      }).catch(() => {
        this.$message({
          type: 'info',
          message: 'Delete canceled',
        });
      });
    },
    async handleEditPermissions(id) {
      this.currentUserId = id;
      this.dialogPermissionLoading = true;
      this.dialogPermissionVisible = true;
      const found = this.list.find(user => user.id === id);
      const { data } = await userResource.permissions(id);
      this.currentUser = {
        id: found.id,
        name: found.name,
        permissions: data,
      };
      this.dialogPermissionLoading = false;
      this.$nextTick(() => {
        this.$refs.menuPermissions.setCheckedKeys(this.permissionKeys(this.userMenuPermissions));
        this.$refs.otherPermissions.setCheckedKeys(this.permissionKeys(this.userOtherPermissions));
      });
    },
    createUser() {
      this.$refs['userForm'].validate((valid) => {
        if (valid) {
          this.newUser.roles = [this.newUser.role];
          this.userCreating = true;
          userResource
            .store(this.newUser)
            .then(response => {
              this.$message({
                message: 'New user ' + this.newUser.name + '(' + this.newUser.email + ') has been created successfully.',
                type: 'success',
                duration: 5 * 1000,
              });
              this.resetNewUser();
              this.dialogFormVisible = false;
              this.handleFilter();
            })
            .catch(error => {
              console.log(error);
            })
            .finally(() => {
              this.userCreating = false;
            });
        } else {
          console.log('error submit!!');
          return false;
        }
      });
    },
    resetNewUser() {
      this.newUser = {
        name: '',
        email: '',
        password: '',
        confirmPassword: '',
        role: 'user',
      };
    },
    handleDownload() {
      this.downloading = true;
      import('@/vendor/Export2Excel').then(excel => {
        const tHeader = ['id', 'user_id', 'name', 'email', 'role'];
        const filterVal = ['index', 'id', 'name', 'email', 'role'];
        const data = this.formatJson(filterVal, this.list);
        excel.export_json_to_excel({
          header: tHeader,
          data,
          filename: 'user-list',
        });
        this.downloading = false;
      });
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map(v => filterVal.map(j => v[j]));
    },
    permissionKeys(permissions) {
      return permissions.map(permssion => permssion.id);
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
      return { id: permission.id, name: this.$options.filters.uppercaseFirst(permission.name.substring(10)), disabled: permission.disabled || false };
    },

    normalizePermission(permission) {
      const disabled = permission.disabled || permission.name === 'manage permission';
      return { id: permission.id, name: this.$options.filters.uppercaseFirst(permission.name), disabled: disabled };
    },

    confirmPermission() {
      const checkedMenu = this.$refs.menuPermissions.getCheckedKeys();
      const checkedOther = this.$refs.otherPermissions.getCheckedKeys();
      const checkedPermissions = checkedMenu.concat(checkedOther);
      this.dialogPermissionLoading = true;

      userResource.updatePermission(this.currentUserId, { permissions: checkedPermissions }).then(response => {
        this.$message({
          message: 'Permissions has been updated successfully',
          type: 'success',
          duration: 5 * 1000,
        });
        this.dialogPermissionLoading = false;
        this.dialogPermissionVisible = false;
      });
    },
  },
};
</script>

<style lang="scss" >
  .b{
    .el-dialog{
      width:500px;
      border-radius:10px;
      .el-dialog__header{
        margin: 26px 30px 0px;
        border-bottom: 1px solid #D3D3D3;
        padding: 24px 0 5.5px 0;
        .el-dialog__title {
          color:#707070;
          font-size:33px;
          font-family: 'Abel', sans-serif;
          font-weight:400;
          line-height:43px;
        }
      }
      .el-dialog__body {
        padding: 21px 30px;
        color: #606266;
        font-size: 20px;
        word-break: break-all;
        .form-container{
          .el-form-item--medium .el-form-item__label {
            color:#707070;
            font-size:16px;
            font-family: 'Ubuntu', sans-serif;
            font-weight:500;
            line-height:11px;
            margin-top: 13px;
          }
          .el-input--medium .el-input__inner {
              height: 34px;
              line-height: 34px;
              font-size: 16px;
          }
          .div_tabel{
            .transaksi_tabel_add{
              width:687px;
              border-spacing: 10px;
              border-collapse: separate;
               thead{
                color:#707070;
                font-size:13px;
                font-family: 'Ubuntu', sans-serif;
                font-weight:400;
                line-height:11px;
                text-align:left;
              }
              tbody{
               color:#707070;
               font-size:13px;
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
                 font-size: 16px;
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
          font-size:16px;
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
  }
.edit-input {
  padding-right: 100px;
}
.cancel-btn {
  position: absolute;
  right: 15px;
  top: 10px;
}
.dialog-footer {
  text-align: left;
  padding-top: 0;
  margin-left: 150px;
}
.app-container {
  flex: 1;
  justify-content: space-between;
  width:96%;
  font-size:16px;
  padding: 16px;
  margin:20px;
  background:#FFFFFF;
  border-radius:10px;
  .el-button--medium.is-circle {
    padding: 8px;
    border: none;
  }
  .filter-container{
    .tabel_filter{
      color:#707070;
      font-size:16px;
      font-family: 'Ubuntu', sans-serif;
      font-weight:300;
      line-height:16px;
      .el-input__inner {
        height: 36px ;
        line-height: 36px;
        font-size:16px;
        border: 1px solid #707070;
      }
      .el-input--medium{
        .el-input__inner {
          height: 36px ;
          line-height: 36px;
          font-size:16px;
          border: 1px solid #707070;
        }
      }
    }
    .el-select.tabel_filter{
      color:#707070;
      font-size:16px;
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
          font-size:16px;
          border: 1px solid #707070;
        }
      }
    }
  }
  .el-table{
      color: #707070;
      font-size:16px;
      font-family: 'Ubuntu', sans-serif;
      font-weight: 400;
      line-height:11px;
      padding:0px !important;
      .el-tag {
          padding: 0 5px;
          line-height: 30px;
          font-family: 'Ubuntu', sans-serif;
          font-size:16px;
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
    font-size:16px;
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
    font-size: 16px;
    font-family: 'Ubuntu', sans-serif;
    font-weight: 500;
    line-height: 11px;
    background-color: transparent;
    border-color: #707070;
    border-radius: 13px;
    float: right;
    padding: 10px;
  }
  .el-button--expexcel {
    color: #FFFFFF;
    font-size:16px;
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
