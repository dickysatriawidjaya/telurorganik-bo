<template>
  <div class="app-container">
    <div class="filter-container">
      <el-row>
        <el-col :span="9">
          <h3 class="text_normal">
            Keyword
          </h3>
        </el-col>
        <el-col :span="3">
          <h3 class="text_normal">
            Status
          </h3>
        </el-col>
      </el-row>
      <el-row>
        <el-col :span="9">
          <el-input v-model="query.keyword" :placeholder="$t('table.keyword')" style="padding-right:26px;" class="filter-item tabel_filter" @input="handleFilter" />
        </el-col>
        <el-col :span="3">
          <el-select v-model="query.status" :placeholder="$t('table.status')" class="filter-item tabel_filter" @change="handleFilter">
            <el-option key="1" label="Active" value="1" />
            <el-option key="-1" label="Deleted" value="-1" />
          </el-select>
        </el-col>
        <el-col :span="12">
          <el-button class="filter-item" style="margin-left: 10px;" type="add" icon="el-icon-plus" @click="handleCreate">
            {{ $t('table.add') }} Item
          </el-button>
        </el-col>
      </el-row>
    </div>

    <el-table v-loading="loading" :data="list" border fit highlight-current-row style="width: 100%">
      <el-table-column align="center" label="No." prop="index" sortable width="80">
        <template slot-scope="scope">
          <span>{{ scope.row.index }}</span>
        </template>
      </el-table-column>

      <el-table-column align="center" label="Item" prop="name" sortable>
        <template slot-scope="scope">
          <span>{{ scope.row.name }}</span>
        </template>
      </el-table-column>

      <el-table-column align="center" label="Unit" prop="unit" sortable>
        <template slot-scope="scope">
          <span>{{ scope.row.unit.name }}</span>
        </template>
      </el-table-column>

      <el-table-column align="right" label="Price" prop="price" sortable>
        <template slot-scope="scope">
          <span>{{ scope.row.price | toCurrency }}</span>
        </template>
      </el-table-column>

      <el-table-column align="center" label="Created Date" prop="created_at" sortable>
        <template slot-scope="scope">
          <span>{{ scope.row.created_at | moment("DD/MMM/YY") }}</span>
        </template>
      </el-table-column>

      <el-table-column class-name="status-col" label="Status" width="110" prop="status" sortable>
        <template slot-scope="scope">
          <el-tag v-if="scope.row.status == 1" type="success">
            ACTIVE
          </el-tag>
          <el-tag v-if="scope.row.status == -1" type="danger">
            DELETED
          </el-tag>
        </template>
      </el-table-column>

      <el-table-column align="center" label="Actions" width="150">
        <template slot-scope="scope">
          <el-button v-permission="['manage user']" size="medium" icon="el-icon-edit" circle @click="handleUpdate(scope.row)" />
          <el-button v-if="scope.row.status == 1" v-permission="['manage user']" size="medium" icon="el-icon-delete" circle @click="handleDelete(scope.row.id, scope.row.name);" />
        </template>
      </el-table-column>
    </el-table>

    <pagination v-show="total>0" :total="total" :page.sync="query.page" :limit.sync="query.limit" @pagination="getList" />

    <el-dialog :title="titleForm" :visible.sync="dialogFormVisible" :before-close="handleClose">
      <div v-loading="itemCreating" class="form-container">
        <el-form ref="itemForm" :model="newItem" label-position="left" label-width="100px" style="max-width: 500px;">
          <el-form-item label="Name" prop="name_form">
            <el-input v-model="newItem.name_form" placeholder="name"/>
            <!-- <span v-if="nameblurred && nameEmpty" class="error">Name form is required!</span> -->
          </el-form-item>
          <el-form-item label="Unit" prop="unit_id_form">
            <el-select v-model="newItem.unit_id_form" placeholder="select unit" style="width: 100%" class="filter-item" @change="forceUpdate">
              <el-option v-for="u in unitList" :key="u.id" :label="u.name" :value="u.id">{{ u.name }}</el-option>
            </el-select>
            <!-- <span v-if="unitblurred && unitEmpty" class="error">Unit ID must be selected!</span> -->
          </el-form-item>
          <el-form-item label="Price" prop="price_form">
            <el-input v-model="newItem.price_form" placeholder="price" @input="forceUpdate" />
            <!-- <span v-if="priceblurred && priceEmpty" class="error">Price is required!</span> -->
          </el-form-item>
          <el-form-item v-if="itemId > 0" label="Status" prop="status">
            <el-select v-model="newItem.status_form" style="width: 100%" class="filter-item" @change="forceUpdate">
              <el-option v-for="s in status" :key="s.value" :label="s.label" :value="s.value">{{ s.label }}</el-option>
            </el-select>
          </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
          <el-button type="canceltransaksi" @click="dialogFormVisible = false, attemptSubmit = false">
            {{ $t('table.cancel') }}
          </el-button>
          <el-button v-if="itemId <= 0" type="addtransaksi" @click="createUser()">
            {{ $t('table.confirm') }}
          </el-button>
          <el-button v-if="itemId > 0" type="addtransaksi" @click="onUpdate()">
            Update
          </el-button>
        </div>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import Pagination from '@/components/Pagination'; // Secondary package based on el-pagination
import ItemResource from '@/api/item';
import UserResource from '@/api/user';
import UnitResource from '@/api/unit';
import Resource from '@/api/resource';
import waves from '@/directive/waves'; // Waves directive
import permission from '@/directive/permission'; // Waves directive
import checkPermission from '@/utils/permission'; // Permission checking

const userResource = new UserResource();
const itemResource = new ItemResource();
const unitResource = new UnitResource();
const permissionResource = new Resource('permissions');

export default {
  name: 'UserList',
  components: { Pagination },
  directives: { waves, permission },
  data() {
    return {
      list: null,
      unitList: null,
      total: 0,
      loading: true,
      downloading: false,
      itemCreating: false,
      query: {
        page: 1,
        limit: 15,
        keyword: '',
        role: '',
        paginate: true,
      },
      queryUnit: {
        paginate: false,
      },
      nonAdminRoles: ['editor', 'user', 'visitor'],
      newItem: {},
      status: [
        { label: 'Active', value: 1 },
        { label: 'Deleted', value: -1 },
      ],
      titleForm: '',
      itemId: 0,
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
        name_form: [{ required: true, message: 'Name form is required!', trigger: 'blur' }],
        unit_id_form: [{ required: true, message: 'Unit ID must be selected!', trigger: 'change' }],
        price_form: [{ required: true, message: 'Price is required!', trigger: 'blur' }],
      },
      permissionProps: {
        children: 'children',
        label: 'name',
        disabled: 'disabled',
      },
      permissions: [],
      menuPermissions: [],
      otherPermissions: [],
      errors: [],
      priceEmpty: false,
      nameEmpty: false,
      unitEmpty: false,
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
    nameblurred: function() {
      return this.newItem.name_form == '';
    },
    priceblurred: function() {
      return this.newItem.price_form == null || this.newItem.price_form == '' || this.newItem.price_form == 0;
    },
    unitblurred: function() {
      return this.newItem.unit_id_form == null;
    },
  },
  created() {
    this.resetnewItem();
    this.getUnitList();
    // if (checkPermission(['manage permission'])) {
    //   this.getPermissions();
    // }
    this.getList();

  },
  methods: {
    handleClose(done) {
      this.$confirm('Are you sure to close this dialog?')
        .then(_ => {
          done();
        })
        .catch(_ => {});
    },
    forceUpdate(){
      this.$forceUpdate();
    },
    // forceUpdate2(){
    //   this.$forceUpdate();
    //   this.$nextTick(() => {
    //     this.$refs['itemForm'].clearValidate();
    //   });
    // },
    checkPermission,
    async getPermissions() {
      const { data } = await permissionResource.list({});
      const { all, menu, other } = this.classifyPermissions(data);
      this.permissions = all;
      this.menuPermissions = menu;
      this.otherPermissions = other;
    },
    async getUnitList() {
      const { limit, page } = this.queryUnit;
      this.loading = true;
      const { data, meta } = await unitResource.list(this.queryUnit);
      this.unitList = data;
      this.list.forEach((element, index) => {
        element['index'] = (page - 1) * limit + index + 1;
      });
      this.total = meta.total;
      this.loading = false;
    },
    async getList() {
      const { limit, page } = this.query;
      this.loading = true;
      const { data, meta } = await itemResource.list(this.query);
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
      this.dialogFormVisible = true;
      this.$nextTick(() => {
        this.$refs['itemForm'].clearValidate();
      });
      this.titleForm = 'Create New Item';
      this.itemId = 0;
      this.resetnewItem();
    },
    handleUpdate(data){
      this.dialogFormVisible = true;
      this.$nextTick(() => {
        this.$refs['itemForm'].clearValidate();
      });
      this.titleForm = 'Edit Item';
      this.itemId = data.id;
      this.newItem.name_form = data.name;
      this.newItem.price_form = data.price;
      this.newItem.unit_id_form = data.unit_id;
      this.newItem.status_form = data.status;
    },
    handleDelete(id, name) {
      this.$confirm('This will delete item ' + "'" + name + "'" + '. Continue?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning',
      }).then(() => {
        itemResource.destroy(id).then(response => {
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
    createUser() {
      this.$refs['itemForm'].validate((valid) => {
        if (valid) {
          // this.priceEmpty = true;
          // this.nameEmpty = true;
          // this.unitEmpty = true;
          // if (this.priceblurred || this.nameblurred || this.unitblurred) {
          //   return true;
          // }
          this.itemCreating = true;
          itemResource
            .store(this.newItem)
            .then(response => {
              this.$message({
                message: 'New item ' + this.newItem.name + ' has been created successfully.',
                type: 'success',
                duration: 5 * 1000,
              });
              this.resetnewItem();
              this.dialogFormVisible = false;
              this.handleFilter();
              this.priceEmpty = false;
              this.nameEmpty = false;
              this.unitEmpty = false;
            })
            .catch(error => {
              if (error.response.status == 403) {
                this.errors = error.response.data.errors;
                console.log(this.errors);
              }
            })
            .finally(() => {
              this.itemCreating = false;
            });
        } else {
          console.log('error submit!!');
          return false;
        }
      });
    },
    onUpdate() {
      // this.$refs['itemForm'].validate((valid) => {
      //   if (valid) {
          // this.priceEmpty = true;
          // this.nameEmpty = true;
          // if (this.priceblurred || this.nameblurred) {
          //   return true;
          // }
          this.itemCreating = true;
          itemResource
            .update(this.itemId, this.newItem)
            .then(response => {
              this.$message({
                message: 'Vendor information has been updated successfully',
                type: 'success',
                duration: 5 * 1000,
              });
              this.resetnewItem();
              this.dialogFormVisible = false;
              this.handleFilter();
              this.attemptSubmit = false;
            })
            .catch(error => {
              if (error.response.status == 403) {
                this.errors = error.response.data.errors;
                console.log(this.errors);
              }
            })
            .finally(() => {
              this.itemCreating = false;
            });
      //   } else {
      //     console.log('error submit!!');
      //     return false;
      //   }
      // });
    },
    resetnewItem() {
      this.newItem = {
        name_form: '',
        phone_form: '',
        address_form: '',
        pic_name_form: '',
        status_form: '',
      };
    },
    handleDownload() {
      this.downloading = true;
      import('@/vendor/Export2Excel').then(excel => {
        const tHeader = ['id', 'name', 'unit', 'status'];
        const filterVal = ['index', 'name', 'unit.name', 'status'];
        const data = this.formatJson(filterVal, this.list);
        excel.export_json_to_excel({
          header: tHeader,
          data,
          filename: 'item-list',
        });
        this.downloading = false;
      });
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map(v => filterVal.map(j => v[j]));
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

<style lang="scss">
.el-dialog{
      width:410px;
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
            color:#707070;
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
    .text_normal{
      font-weight: 500 !important;
      color: #707070;
      font-size: 16px;
      font-family: 'Ubuntu', sans-serif;
      font-weight: 500;
      line-height: 29px;
      padding: 0;
      margin: 0;
    }
    .tabel_filter{
      color:#707070;
      font-size:16px;
      font-family: 'Ubuntu', sans-serif;
      font-weight:300;
      line-height:16px;
      .el-input__inner {
        color:#707070;
        height: 36px ;
        line-height: 36px;
        font-size:16px;
        border: 1px solid #D3D3D3 !important;
      }
      .el-input--medium{
        .el-input__inner {
          color:#707070;
          height: 36px ;
          line-height: 36px;
          font-size:16px;
          border: 1px solid #D3D3D3 !important;
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
          color:#707070;
          height: 36px;
          line-height: 36px;
          font-size:16px;
          border: 1px solid #D3D3D3;
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
          font-size: 16px;
      }
      .cell {
        box-sizing: border-box;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: normal;
        word-break: break-all;
        line-height: qqpx;
        padding-left: 5px;
        padding-right: 5px;
      }
      .status-col .cell {
        padding: 0 10px;
        text-align: left;
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
    border-radius: 19px;
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
    font-size: 16px;
    font-family: 'Ubuntu', sans-serif;
    font-weight: 500;
    line-height: 14px;
    background-color: #46A2FD;
    border-radius:5px;
    padding:8px 33px;
  }
  .error {
    float: left;
    font-size: 12px;
    line-height: normal;
    padding-top: 2px;
    color: red;
    opacity: 0.6;
  }
  .highlight {
    input {
      border-color: red;
    }
  }
}
</style>
