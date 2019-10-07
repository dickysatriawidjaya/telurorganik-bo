<template>
  <div class="app-container">
    <div class="filter-container">
      <el-input v-model="query.keyword" :placeholder="$t('table.keyword')" style="width: 200px;" class="filter-item" @input="handleFilter" />
      <el-select v-model="query.status" :placeholder="$t('table.status')" clearable style="width: 150px" class="filter-item" @change="handleFilter">
        <el-option key="1" label="PRINTED" value="1" />
        <el-option key="0" label="WAITING" value="0" />
      </el-select>
      <el-button class="filter-item" style="margin-left: 10px;" type="primary" icon="el-icon-plus" @click="handleCreate">
        {{ $t('table.add') }}
      </el-button>
      <el-button v-waves :loading="downloading" class="filter-item" type="primary" icon="el-icon-download" @click="handleDownload">
        {{ $t('table.export') }} Excel <svg-icon icon-class="excel" />
      </el-button>
    </div>

    <el-table v-loading="loading" :data="list" border fit highlight-current-row style="width: 100%">
      <el-table-column align="center" label="ID" prop="index" sortable width="80">
        <template slot-scope="scope">
          <span>{{ scope.row.index }}</span>
        </template>
      </el-table-column>

      <el-table-column align="center" label="Transaction No." prop="transaction_no" sortable>
        <template slot-scope="scope">
          <span>{{ scope.row.transaction_no }}</span>
        </template>
      </el-table-column>

      <el-table-column align="center" label="Vendor" prop="vendor" sortable>
        <template slot-scope="scope">
          <span v-if="scope.row.vendor.pic_name != '' || scope.row.vendor.pic_name">{{ scope.row.vendor.name }} ( {{ scope.row.vendor.pic_name }} )</span>
          <span v-else>{{ scope.row.vendor.name }}</span>
        </template>
      </el-table-column>

      <el-table-column align="center" label="Total" prop="total" sortable>
        <template slot-scope="scope">
          <span>{{ scope.row.total | toCurrency }}</span>
        </template>
      </el-table-column>

      <el-table-column class-name="status-col" label="Status" width="110" prop="status" sortable>
        <template slot-scope="scope">
          <el-tag v-if="scope.row.status == 1" type="success">
            PRINTED
          </el-tag>
          <el-tag v-if="scope.row.status == 0" type="warning">
            WAITING
          </el-tag>
        </template>
      </el-table-column>

      <el-table-column align="center" label="Actions" width="350">
        <template slot-scope="scope">
          <router-link :to="'/administrator/users/edit/'+scope.row.id">
            <el-button v-permission="['manage user']" type="primary" size="small" icon="el-icon-edit">
              Detail
            </el-button>
          </router-link>
          <el-button v-permission="['manage user']" type="primary" size="small" icon="el-icon-edit" @click="handleUpdate(scope.row)">
            Edit
          </el-button>
          <el-button v-if="scope.row.status == -1" v-permission="['manage user']" type="danger" size="small" icon="el-icon-delete" @click="handleDelete(scope.row.id, scope.row.name);">
            Delete
          </el-button>
        </template>
      </el-table-column>
    </el-table>

    <pagination v-show="total>0" :total="total" :page.sync="query.page" :limit.sync="query.limit" @pagination="getList" />

    <el-dialog :title="titleForm" :visible.sync="dialogFormVisible">
      <div v-loading="transactionCreating" class="form-container">
        <el-form ref="itemForm" :rules="rules" :model="newTransaction" label-position="left" label-width="150px" style="max-width: 500px;">
          <el-form-item label="Transaction No" prop="transaction_no">
            <el-input v-model="newTransaction.transaction_no_form" />
          </el-form-item>
          <el-form-item label="Vendor" prop="vendor">
            <el-select v-model="newTransaction.vendor_form" filterable class="filter-item">
              <el-option v-for="v in vendorList" :key="v.id" :label="v.name" :value="v.id">
                <span style="float: left">{{ v.name }}</span>
                <span style="float: right; color: #8492a6; font-size: 13px">{{ (v.pic_name)?v.pic_name:"-" }}</span>
              </el-option>
            </el-select>
          </el-form-item>

          <el-form-item v-if="itemId > 0" label="Status" prop="status">
            <el-select v-model="newTransaction.status_form" style="width: 150px" class="filter-item">
              <el-option v-for="s in status" :key="s.value" :label="s.label" :value="s.value">{{ s.label }}</el-option>
            </el-select>
          </el-form-item>
          <el-form-item>
            Detail Transaction
          </el-form-item>

          <hr>

          <el-form-item label="Add Item">
            <el-button @click="addNewItemForm"> + </el-button>
          </el-form-item>

          <table>
            <thead>
              <tr>
                <th>No.</th>
                <th>Item</th>
                <th>Quantity</th>
                <th>Discount</th>
                <th>Total</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>asd</td>
                <td>asd</td>
                <td>asd</td>
                <td>asd</td>
              </tr>
            </tbody>
          </table>

          <hr>

          <el-form-item label="Total Price" prop="total">
            <el-input v-show="false" v-model="newTransaction.total_form" />
            {{ newTransaction.total_form }}
          </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
          <el-button @click="dialogFormVisible = false">
            {{ $t('table.cancel') }}
          </el-button>
          <el-button v-if="itemId <= 0" type="primary" @click="createUser()">
            {{ $t('table.confirm') }}
          </el-button>
          <el-button v-if="itemId > 0" type="primary" @click="onUpdate()">
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
import VendorResource from '@/api/vendor';
import TransactionResource from '@/api/transaction';
import Resource from '@/api/resource';
import waves from '@/directive/waves'; // Waves directive
import permission from '@/directive/permission'; // Waves directive
import checkPermission from '@/utils/permission'; // Permission checking

const userResource = new UserResource();
const itemResource = new ItemResource();
const vendorResource = new VendorResource();
const transactionResource = new TransactionResource();
const permissionResource = new Resource('permissions');

export default {
  name: 'UserList',
  components: { Pagination },
  directives: { waves, permission },
  data() {
    return {
      list: null,
      vendorList: null,
      detailTransactionForm: null,
      total: 0,
      loading: true,
      downloading: false,
      transactionCreating: false,
      query: {
        page: 1,
        limit: 15,
        keyword: '',
        role: '',
      },
      queryVendor: {
        paginate: false,
      },
      nonAdminRoles: ['editor', 'user', 'visitor'],
      newTransaction: {},
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
    this.resetnewTransaction();
    this.getList();
    this.getVendorList();
    if (checkPermission(['manage permission'])) {
      this.getPermissions();
    }
  },
  methods: {
    addNewItemForm(){
      console.log('masuk');
    },
    checkPermission,
    async getPermissions() {
      const { data } = await permissionResource.list({});
      const { all, menu, other } = this.classifyPermissions(data);
      this.permissions = all;
      this.menuPermissions = menu;
      this.otherPermissions = other;
    },
    async getVendorList() {
      const { limit, page } = this.queryVendor;
      this.loading = true;
      const { data, meta } = await vendorResource.list(this.queryVendor);
      this.vendorList = data;
      this.list.forEach((element, index) => {
        element['index'] = (page - 1) * limit + index + 1;
      });
      this.total = meta.total;
      this.loading = false;
    },
    async getList() {
      const { limit, page } = this.query;
      this.loading = true;
      const { data, meta } = await transactionResource.list(this.query);
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
      this.titleForm = 'Create New Transaction';
      this.itemId = 0;
      this.resetnewTransaction();
    },
    handleUpdate(data){
      this.dialogFormVisible = true;
      this.$nextTick(() => {
        this.$refs['itemForm'].clearValidate();
      });
      this.titleForm = 'Edit Item';
      this.itemId = data.id;
      this.newTransaction.name_form = data.name;
      this.newTransaction.unit_id_form = data.unit_id;
      this.newTransaction.status_form = data.status;
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
          this.transactionCreating = true;
          itemResource
            .store(this.newTransaction)
            .then(response => {
              this.$message({
                message: 'New item ' + this.newTransaction.name + ' has been created successfully.',
                type: 'success',
                duration: 5 * 1000,
              });
              this.resetnewTransaction();
              this.dialogFormVisible = false;
              this.handleFilter();
            })
            .catch(error => {
              console.log(error);
            })
            .finally(() => {
              this.transactionCreating = false;
            });
        } else {
          console.log('error submit!!');
          return false;
        }
      });
    },
    onUpdate() {
      // this.transactionCreating = true;
      // itemResource
      //   .update(this.itemId, this.newTransaction)
      //   .then(response => {
      //     this.transactionCreating = false;
      //     this.$message({
      //       message: 'Vendor information has been updated successfully',
      //       type: 'success',
      //       duration: 5 * 1000,
      //     });
      //   })
      //   .catch(error => {
      //     console.log(error);
      //     this.transactionCreating = false;
      //   });

      this.$refs['itemForm'].validate((valid) => {
        if (valid) {
          this.transactionCreating = true;
          itemResource
            .update(this.itemId, this.newTransaction)
            .then(response => {
              this.$message({
                message: 'Vendor information has been updated successfully',
                type: 'success',
                duration: 5 * 1000,
              });
              this.resetnewTransaction();
              this.dialogFormVisible = false;
              this.handleFilter();
            })
            .catch(error => {
              console.log(error);
            })
            .finally(() => {
              this.transactionCreating = false;
            });
        } else {
          console.log('error submit!!');
          return false;
        }
      });
    },
    resetnewTransaction() {
      this.newTransaction = {
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

<style lang="scss" scoped>
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
  font-size: 14px;
  padding-right: 8px;
  .block {
    float: left;
    min-width: 250px;
  }
  .clear-left {
    clear: left;
  }
}
</style>
