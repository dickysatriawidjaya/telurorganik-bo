<template>
  <div class="app-container">
    <div class="filter-container">
      <el-input v-model="query.keyword" :placeholder="$t('table.keyword')" style="width: 200px;margin-right:16px;" class="filter-item" @input="handleFilter" />
      <el-select v-model="query.status" :placeholder="$t('table.status')" clearable style="width: 150px" class="filter-item" @change="handleFilter">
        <el-option key="1" label="Active" value="1" />
        <el-option key="-1" label="Deleted" value="-1" />
      </el-select>
      <el-button class="filter-item" style="margin-left: 10px;" type="add" icon="el-icon-plus" @click="handleCreate">
        {{ $t('table.add') }} Vendor
      </el-button>
    </div>

    <el-table v-loading="loading" :data="list" border fit highlight-current-row style="width: 100%">
      <el-table-column align="left" label="No." prop="index" width="80">
        <template slot-scope="scope">
          <span>{{ scope.row.index }}</span>
        </template>
      </el-table-column>

      <el-table-column align="left" label="Vendor" prop="name" sortable>
        <template slot-scope="scope">
          <span>{{ scope.row.name }}</span>
        </template>
      </el-table-column>

      <el-table-column align="left" label="Parent" prop="parent" sortable>
        <template slot-scope="scope">
          <span v-if="scope.row.parent != null">{{ scope.row.parent.name }}</span>
          <span v-else>Is Parent</span>
        </template>
      </el-table-column>

      <el-table-column align="left" label="PIC" prop="pic_name" sortable width="120">
        <template slot-scope="scope">
          <span>{{ scope.row.pic_name }}</span>
        </template>
      </el-table-column>

      <el-table-column align="center" label="Phone" prop="phone" sortable>
        <template slot-scope="scope">
          <span>{{ scope.row.phone }}</span>
        </template>
      </el-table-column>

      <el-table-column align="left" label="Address" prop="address" sortable>
        <template slot-scope="scope">
          <span>{{ scope.row.address }}</span>
        </template>
      </el-table-column>

      <el-table-column align="left" label="Date" prop="created_at" sortable>
        <template slot-scope="scope">
          <span>{{ scope.row.created_at | moment("DD-MM-YYYY") }}</span>
        </template>
      </el-table-column>

      <el-table-column align="left" class-name="status-col" label="Status" width="110" prop="status" sortable>
        <template slot-scope="scope">
          <el-tag v-if="scope.row.status == 1" type="success">
            ACTIVE
          </el-tag>
          <el-tag v-if="scope.row.status == -1" type="danger">
            DELETED
          </el-tag>
        </template>
      </el-table-column>

      <el-table-column align="left" label="Actions" width="100">
        <template slot-scope="scope">
          <el-button v-permission="['manage user']" size="small" icon="el-icon-edit" circle @click="handleUpdate(scope.row)" />
          <el-button v-if="scope.row.status == 1" v-permission="['manage user']" size="small" icon="el-icon-delete" circle @click="handleDelete(scope.row.id, scope.row.name);" />
        </template>
      </el-table-column>
    </el-table>

    <pagination v-show="total>0" :total="total" :page.sync="query.page" :limit.sync="query.limit" @pagination="getList" />

    <el-dialog :title="titleForm" :visible.sync="dialogFormVisible" class="a" width="850px" :before-close="handleClose">
      <div v-loading="vendorCreating" class="form-container">
        <el-form ref="vendorForm" :rules="rules" :model="newVendor" label-position="left" label-width="150px" style="max-width: 850px;">
          <el-row>
            <el-col :span="12">
              <el-form-item label="Name" prop="name">
                <el-input v-model="newVendor.name_form" placeholder="Name" :class="{'highlight':venNameBlurred && attemptSubmit}" />
                <span v-if="venNameBlurred && attemptSubmit" class="error">Vendor name is required!</span>
              </el-form-item>
              <el-form-item label="PIC Name" prop="pic_name">
                <el-input v-model="newVendor.pic_name_form" placeholder="PIC Name" :class="{'highlight':venPICBlurred && attemptSubmit}"/>
                <span v-if="venPICBlurred && attemptSubmit" class="error">PIC Name is required!</span>
              </el-form-item>
              <el-form-item label="Phone" prop="phone">
                <el-input v-model="newVendor.phone_form" placeholder="Phone Number" type="number" :class="{'highlight':venPhoneBlurred && attemptSubmit}" />
                <span v-if="venPhoneBlurred && attemptSubmit" class="error">Vendor phone is required!</span>
              </el-form-item>
              <el-form-item label="Address" prop="address">
                <el-input v-model="newVendor.address_form" placeholder="Address" type="textarea" rows="3" :class="{'highlight':venAddressBlurred && attemptSubmit}" />
                <span v-if="venAddressBlurred && attemptSubmit" class="error">Vendor phone is required!</span>
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item v-if="vendorId > 0" label="Status" prop="status">
                <el-select v-model="newVendor.status_form" style="width: 150px" class="filter-item">
                  <el-option v-for="s in status" :key="s.value" :label="s.label" :value="s.value">{{ s.label }}</el-option>
                </el-select>
              </el-form-item>
            </el-col>
          </el-row>
        </el-form>

        <div v-show="hasChild" class="div_tabel">
          Add Child Vendor
          <hr>
          <table class="transaksi_tabel_add">
            <thead>
              <tr>
                <th>No.</th>
                <th>Vendor Name</th>
                <th>PIC Name</th>
                <th>Phone</th>
                <th>Address</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(c,index) in newVendor.child">
                <td style="width:30px">
                  {{ index + 1 }}
                </td>
                <td>
                  <el-input v-model="newVendor.child[index].name_form" placeholder="Name"/>
                </td>
                <td>
                  <el-input v-model="newVendor.child[index].pic_name_form" placeholder="PIC Name"/>
                </td>
                <td>
                  <el-input v-model="newVendor.child[index].phone_form" placeholder="Phone"/>
                </td>
                <td>
                  <el-input v-model="newVendor.child[index].address_form" placeholder="Address"/>
                </td>
                <td>
                  <el-button type="danger" icon="el-icon-close" circle @click="spliceVendorChild(index)" />
                </td>
              </tr>
            </tbody>
          </table>
          <hr>
          <el-button type="addtable" @click="addNewVendorForm">Add Child Vendor</el-button>
        </div>
        <div slot="footer" class="dialog-footer">
          <el-button type="canceltransaksi" @click="dialogFormVisible = false">
            {{ $t('table.cancel') }}
          </el-button>
          <el-button v-if="vendorId <= 0" type="addtransaksi" @click="createUser()">
            {{ $t('table.confirm') }}
          </el-button>
          <el-button v-if="vendorId > 0" type="addtransaksi" @click="onUpdate()">
            Update
          </el-button>
        </div>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import Pagination from '@/components/Pagination'; // Secondary package based on el-pagination
import UserResource from '@/api/user';
import VendorResource from '@/api/vendor';
import Resource from '@/api/resource';
import waves from '@/directive/waves'; // Waves directive
import permission from '@/directive/permission'; // Waves directive
import checkPermission from '@/utils/permission'; // Permission checking

const userResource = new UserResource();
const vendorResource = new VendorResource();
const permissionResource = new Resource('permissions');
import Cookies from 'js-cookie';

export default {
  name: 'UserList',
  components: { Pagination },
  directives: { waves, permission },
  data() {
    return {
      list: null,
      total: 0,
      loading: true,
      downloading: false,
      vendorCreating: false,
      hasChild: true,
      query: {
        page: 1,
        limit: 15,
        keyword: '',
        role: Cookies.get('Role'),
        paginate: true,
      },
      nonAdminRoles: ['editor', 'user', 'visitor'],
      newVendor: {
        address_form: '',
        name_form: '',
        phone_form: '',
        pic_name_form: '',
        status_form: '',
        tai: '',
        child: [],
      },
      status: [
        { label: 'Active', value: 1 },
        { label: 'Deleted', value: -1 },
      ],
      titleForm: '',
      vendorId: 0,
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
        name_form: [{ required: true, message: 'Name is required', trigger: 'blur' }],
        phone_form: [
          { required: true, message: 'Phone number is required', trigger: 'blur' },
        ],
        address_form: [{ required: true, message: 'Address is required', trigger: 'blur' }],
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
      attemptSubmit: false,
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
    venNameBlurred: function() {
      return this.newVendor.name_form == '' || this.newVendor.name_form == null;
    },
    venPhoneBlurred: function() {
      return this.newVendor.phone_form == null || this.newVendor.phone_form == '' || this.newVendor.phone_form == 0;
    },
    venAddressBlurred: function() {
      return this.newVendor.address_form == null || this.newVendor.address_form == '';
    },
    venPICBlurred: function() {
      return this.newVendor.pic_name_form == null || this.newVendor.pic_name_form == '';
    },
  },
  created() {
    this.resetnewVendor();
    this.getList();
    if (checkPermission(['manage permission'])) {
      this.getPermissions();
    }
    console.log(this.newVendor);
  },
  methods: {
    spliceVendorChild(index){
      this.newVendor.child.splice(index, 1);
    },
    addNewVendorForm(){
      this.newVendor.child.push(
        {
          name_form: '',
          phone_form: '',
          address_form: '',
          pic_name_form: '',
        }
      );
      console.log(this.newVendor.child.length);
    },
    handleClose(done) {
      this.$confirm('Are you sure to close this dialog?')
        .then(_ => {
          done();
        })
        .catch(_ => {});
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
      const { data, meta } = await vendorResource.list(this.query);
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
        this.$refs['vendorForm'].clearValidate();
      });
      this.titleForm = 'Create New Vendor';
      this.vendorId = 0;
      this.resetnewVendor();
    },
    handleUpdate(data){
      this.newVendor.child = [];
      this.dialogFormVisible = true;
      this.$nextTick(() => {
        this.$refs['vendorForm'].clearValidate();
      });
      this.titleForm = 'Edit Vendor';
      this.vendorId = data.id;
      this.newVendor.name_form = data.name;
      this.newVendor.pic_name_form = data.pic_name;
      this.newVendor.phone_form = data.phone;
      this.newVendor.address_form = data.address;
      this.newVendor.status_form = data.status;

      if (data.parent_id == 0) {
        this.hasChild = true;
      } else {
        this.hasChild = false;
      }

      if (data.child.length > 0) {
        const tmp = [];
        data.child.forEach(function(element, i) {
          tmp.push(
            {
              'id': element.id,
              'name_form': element.name,
              'pic_name_form': element.pic_name,
              'phone_form': element.phone,
              'address_form': element.address,
              'status_form': element.status,
            }
          );
        });

        this.newVendor.child = tmp;
      }
    },
    handleDelete(id, name) {
      this.$confirm('This will delete vendor ' + "'" + name + "'" + '. Continue?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning',
      }).then(() => {
        vendorResource.destroy(id).then(response => {
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
      this.attemptSubmit = true;
      if(this.venNameBlurred || this.venPhoneBlurred || this.venAddressBlurred || this.venPICBlurred) {
        return true
      }
      this.$refs['vendorForm'].validate((valid) => {
        if (valid) {
          this.vendorCreating = true;
          vendorResource
            .store(this.newVendor)
            .then(response => {
              this.$message({
                message: 'New vendor ' + this.newVendor.name + ' has been created successfully.',
                type: 'success',
                duration: 5 * 1000,
              });
              this.resetnewVendor();
              this.dialogFormVisible = false;
              this.handleFilter();
            })
            .catch(error => {
              if (error.response.status == 403) {
                this.errors = error.response.data.errors;
                console.log(this.errors);
              }
            })
            .finally(() => {
              this.vendorCreating = false;
            });
        } else {
          console.log('error submit!!');
          return false;
        }
      });
    },
    onUpdate() {
      // this.vendorCreating = true;
      // vendorResource
      //   .update(this.vendorId, this.newVendor)
      //   .then(response => {
      //     this.vendorCreating = false;
      //     this.$message({
      //       message: 'Vendor information has been updated successfully',
      //       type: 'success',
      //       duration: 5 * 1000,
      //     });
      //   })
      //   .catch(error => {
      //     console.log(error);
      //     this.vendorCreating = false;
      //   });
      this.attemptSubmit = true;
      if(this.venNameBlurred || this.venPhoneBlurred || this.venAddressBlurred || this.venPICBlurred) {
        return true
      }
      this.$refs['vendorForm'].validate((valid) => {
        if (valid) {
          this.vendorCreating = true;
          vendorResource
            .update(this.vendorId, this.newVendor)
            .then(response => {
              this.$message({
                message: 'Vendor information has been updated successfully',
                type: 'success',
                duration: 5 * 1000,
              });
              this.resetnewVendor();
              this.dialogFormVisible = false;
              this.handleFilter();
            })
            .catch(error => {
              if (error.response.status == 403) {
                this.errors = error.response.data.errors;
                console.log(this.errors);
              }
            })
            .finally(() => {
              this.vendorCreating = false;
            });
        } else {
          console.log('error submit!!');
          return false;
        }
      });
    },
    resetnewVendor() {
      this.newVendor = {
        name_form: '',
        phone_form: '',
        address_form: '',
        pic_name_form: '',
        status_form: '',
        child: [],
      };
    },
    handleDownload() {
      this.downloading = true;
      import('@/vendor/Export2Excel').then(excel => {
        const tHeader = ['id', 'name', 'pic_name', 'phone', 'address', 'status'];
        const filterVal = ['index', 'name', 'pic_name', 'phone', 'address', 'status'];
        const data = this.formatJson(filterVal, this.list);
        excel.export_json_to_excel({
          header: tHeader,
          data,
          filename: 'vendor-list',
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
  .a{
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
          .el-form {
            .el-row {
              .el-col {
                padding-right: 30px;
              }
            }
          }
          .div_tabel {
            color: #707070;
            font-size: 33px;
            font-family: 'Abel', sans-serif;
            font-weight: 400;
            line-height: 43px;
          }
          .el-form-item--medium .el-form-item__label {
            color:#707070;
            font-size:16px;
            font-family: 'Ubuntu', sans-serif;
            font-weight:500;
            line-height:11px;
            margin-top: 13px;
          }
          .el-input--medium .el-input__inner, .el-textarea__inner {
              height: 34px;
              line-height: 34px;
              font-size: 16px;
              background-color: #F4F4F4;
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
    border-radius: 19px;
    padding: 10px 11px;
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
    font-size: 16px;
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
    input, textarea {
      border-color: red;
    }
  }
}

</style>
