<template>
  <div class="app-container">
    <div class="filter-container">
      <el-input v-model="query.keyword" :placeholder="$t('table.keyword')" style="width: 200px;" class="filter-item" @input="handleFilter" />
      <el-select v-model="query.status" :placeholder="$t('table.status')" clearable style="width: 150px" class="filter-item" @change="handleFilter">
        <el-option key="1" label="Active" value="1" />
        <el-option key="-1" label="Deleted" value="-1" />
      </el-select>
      <!-- <el-button v-waves class="filter-item" type="primary" icon="el-icon-search" @click="handleFilter">
        {{ $t('table.search') }}
      </el-button> -->
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

      <el-table-column align="center" label="Name" prop="name" sortable>
        <template slot-scope="scope">
          <span>{{ scope.row.name }}</span>
        </template>
      </el-table-column>

      <el-table-column align="center" label="PIC Name" prop="pic_name" sortable>
        <template slot-scope="scope">
          <span>{{ scope.row.pic_name }}</span>
        </template>
      </el-table-column>

      <el-table-column align="center" label="phone" prop="phone" sortable>
        <template slot-scope="scope">
          <span>{{ scope.row.phone }}</span>
        </template>
      </el-table-column>

      <el-table-column align="center" label="Address" prop="address" sortable>
        <template slot-scope="scope">
          <span>{{ scope.row.address }}</span>
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

      <!-- <el-table-column align="center" label="Role" width="120">
        <template slot-scope="scope">
          <span>{{ scope.row.roles.join(', ') }}</span>
        </template>
      </el-table-column> -->

      <el-table-column align="center" label="Actions" width="350">
        <template slot-scope="scope">
          <el-button v-permission="['manage user']" type="primary" size="small" icon="el-icon-edit" @click="handleUpdate(scope.row)">
            Edit
          </el-button>
          <el-button v-permission="['manage user']" type="danger" size="small" icon="el-icon-delete" @click="handleDelete(scope.row.id, scope.row.name);">
            Delete
          </el-button>
        </template>
      </el-table-column>
    </el-table>

    <pagination v-show="total>0" :total="total" :page.sync="query.page" :limit.sync="query.limit" @pagination="getList" />

    <!-- <el-dialog :visible.sync="dialogPermissionVisible" :title="'Edit Permissions - ' + currentUser.name">
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
          <el-button type="danger" @click="dialogPermissionVisible=false">
            {{ $t('permission.cancel') }}
          </el-button>
          <el-button type="primary" @click="confirmPermission">
            {{ $t('permission.confirm') }}
          </el-button>
        </div>
      </div>
    </el-dialog> -->

    <el-dialog :title="titleForm" :visible.sync="dialogFormVisible">
      <div v-loading="vendorCreating" class="form-container">
        <el-form ref="vendorForm" :rules="rules" :model="newVendor" label-position="left" label-width="150px" style="max-width: 500px;">
          <el-form-item label="Name" prop="name">
            <el-input v-model="newVendor.name_form" />
          </el-form-item>
          <el-form-item label="PIC Name" prop="pic_name">
            <el-input v-model="newVendor.pic_name_form" />
          </el-form-item>
          <el-form-item label="Phone" prop="phone">
            <el-input v-model="newVendor.phone_form" />
          </el-form-item>
          <el-form-item label="Address" prop="address">
            <el-input v-model="newVendor.address_form" />
          </el-form-item>
          <el-form-item v-if="vendorId > 0" label="Status" prop="status">
            <el-select v-model="newVendor.status_form" style="width: 150px" class="filter-item">
              <el-option v-for="s in status" :key="s.value" :label="s.label" :value="s.value">{{ s.label }}</el-option>
            </el-select>
          </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
          <el-button @click="dialogFormVisible = false">
            {{ $t('table.cancel') }}
          </el-button>
          <el-button v-if="vendorId <= 0" type="primary" @click="createUser()">
            {{ $t('table.confirm') }}
          </el-button>
          <el-button v-if="vendorId > 0" type="primary" @click="onUpdate()">
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
      query: {
        page: 1,
        limit: 15,
        keyword: '',
        role: '',
      },
      nonAdminRoles: ['editor', 'user', 'visitor'],
      newVendor: {},
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
    this.resetnewVendor();
    this.getList();
    if (checkPermission(['manage permission'])) {
      this.getPermissions();
    }
  },
  methods: {
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
      this.titleForm = 'Create new vendor';
      this.vendorId = 0;
      this.resetnewVendor();
    },
    handleUpdate(data){
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
              console.log(error);
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
              console.log(error);
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
