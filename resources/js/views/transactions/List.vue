<template>
  <div class="app-container">
    <div class="filter-container">

      <el-input v-model="query.keyword" :placeholder="$t('table.keyword')" style="width: 250px;" class="filter-item tabel_filter" @input="handleFilter" />

      <el-select v-model="query.status" :placeholder="$t('table.status')" clearable style="width: 100px" class="filter-item tabel_filter" @change="handleFilter">
        <el-option key="1" label="Paid" value="1" />
        <el-option key="-1" label="Unpaid" value="-1" />
      </el-select>

      <el-select v-model="query.vendor" placeholder="Vendor" clearable style="width: 200px" class="filter-item tabel_filter" @change="handleFilter">
        <el-option v-for="v in vendorList" :key="v.id" :label="v.name" :value="v.id">
          <span style="float: left">{{ v.name }}</span>
          <span style="float: right; color: #8492a6; font-size: 13px">{{ (v.pic_name)?v.pic_name:"-" }}</span>
        </el-option>
      </el-select>

      <el-button class="filter-item is-right"  type="add" icon="el-icon-plus" @click="handleCreate">
        {{ $t('table.add') }} Transaction
      </el-button>

    </div>

    <el-table v-loading="loading" :data="list" border fit highlight-current-row style="width: 100%">
      <el-table-column align="left" label="No." prop="index" width="60">
        <template slot-scope="scope">
          <span>{{ scope.row.index }}</span>
        </template>
      </el-table-column>
      <el-table-column align="left" class-name="status-col" label="Date" prop="created_at" sortable width="120">
        <template slot-scope="scope">
          <span>{{ scope.row.created_at | moment("DD-MM-YYYY") }}</span>
        </template>
      </el-table-column>
      <el-table-column align="left" label="Vendor" prop="vendor" sortable>
        <template slot-scope="scope">
          <span v-if="scope.row.vendor.pic_name != '' || scope.row.vendor.pic_name">{{ scope.row.vendor.name }} ( {{ scope.row.vendor.pic_name }} )</span>
          <span v-else>{{ scope.row.vendor.name }}</span>
        </template>
      </el-table-column>
      <el-table-column align="left" label="Trans.ID" prop="transaction_no" sortable width="140">
        <template slot-scope="scope">
          <span>{{ scope.row.transaction_no }}</span>
        </template>
      </el-table-column>
      <el-table-column align="left" label="Total" prop="total" sortable>
        <template slot-scope="scope">
          <span>{{ scope.row.total | toCurrency }}</span>
        </template>
      </el-table-column>
      <!-- <el-table-column align="center" label="detail" prop="transaction_detail" sortable>
        <template slot-scope="scope">
          <span>{{ scope.row.transaction_detail }}</span>
        </template>
      </el-table-column> -->
      <el-table-column align="left" class-name="status-col" label="Status" prop="status" width="100">
        <template slot-scope="scope">
          <span v-if="scope.row.status == 1" style="color:#46A2FD">
            Paid
          </span>
          <span v-if="scope.row.status == -1" style="color:#FD4646 !important">
            Unpaid
          </span>
        </template>
      </el-table-column>

      <el-table-column align="left" label="Actions" width="100">
        <template slot-scope="scope">
          <router-link :to="'/transaction/detail/'+scope.row.id">
            <el-button v-permission="['manage user']" size="medium" icon="el-icon-document" circle />
          </router-link>
          <el-button v-permission="['manage user']" size="medium" icon="el-icon-edit" circle @click="handleUpdate(scope.row)" />
          <!--
          <el-button v-if="scope.row.status == -1" v-permission="['manage user']" type="primary" size="small" icon="el-icon-edit" @click="handleUpdateStatus(scope.row.id,1)">
            Set LUNAS
          </el-button>

          <el-button v-if="scope.row.status == 1" v-permission="['manage user']" type="primary" size="small" icon="el-icon-edit" @click="handleUpdateStatus(scope.row.id,-1)">
            Set BELUM LUNAS
          </el-button> -->

        </template>
      </el-table-column>
    </el-table>

    <pagination v-show="total>0" :total="total" :page.sync="query.page" :limit.sync="query.limit" @pagination="getList" />

    <el-dialog :title="titleForm" :visible.sync="dialogFormVisible">
      <div v-loading="transactionCreating" class="form-container">
        <el-form ref="itemForm" :rules="rules" :model="newTransaction" label-position="left" label-width="150px">
          <el-row :gutter="20">
            <el-col :span="12">
              <el-form-item label="Transaction No" prop="transaction_no">
<!-- <<<<<<< HEAD -->
                <el-input v-model="newTransaction.transaction_no_form" style="width:200px"  @input="forceupdate"/>
              </el-form-item>
              <el-form-item label="Transaction Date" prop="transaction_date">
                <el-date-picker
                  v-model="newTransaction.date_form"
                  type="datetime"
                  value-format="yyyy-MM-dd"
                  placeholder="Select date and time" @input="forceupdate">
                </el-date-picker>
              </el-form-item>
              <el-form-item label="Vendor" prop="vendor">
                <el-select v-model="newTransaction.vendor_id_form" filterable class="filter-item" style="width:200px" @change="forceupdate">
<!-- =======
                <el-input v-model="newTransaction.transaction_no_form" style="width:200px"/>
              </el-form-item>
              <el-form-item label="Vendor" prop="vendor">
                <el-select v-model="newTransaction.vendor_id_form" filterable class="filter-item" style="width:200px" @change="forceupdate">
>>>>>>> 6e6304b238c7181c7d8a6805617297411c063636 -->
                  <el-option v-for="v in vendorList" :key="v.id" :label="v.name" :value="v.id">
                    <span style="float: left">{{ v.name }}</span>
                    <span style="float: right; color: #8492a6; font-size: 13px">{{ (v.pic_name)?v.pic_name:"-" }}</span>
                  </el-option>
                </el-select>
              </el-form-item>
            </el-col>
            <el-col :span="11">
              <el-form-item v-if="transactionId > 0" label="Status" prop="status">
                <el-select v-model="newTransaction.status_form" style="width: 150px" class="filter-item" @change="forceupdate">
                  <el-option v-for="s in status" :key="s.value" :label="s.label" :value="s.value">{{ s.label }}</el-option>
                </el-select>
              </el-form-item>
            </el-col>
          </el-row>
          <!-- <el-form-item>
            Detail Transaction
          </el-form-item>
          <hr> -->

          <div class="div_tabel">
            <hr>
            <table class="transaksi_tabel_add">
              <thead>
                <tr>
                  <th>No.</th>
                  <th>Item</th>
                  <th>Price</th>
                  <th>Quantity</th>
                  <th>Discount</th>
                  <th>Total</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(d,index) in newTransaction.detail">
                  <td style="width:30px">
                    {{ index + 1 }}
                  </td>
                  <td style="width:212px">
                    <el-select v-model="newTransaction.detail[index].item_id_form" class="filter-item" @change="setItemPrice(index)">
                      <el-option v-for="i in itemList" :key="i.id" :label="i.name+'('+i.unit.name+')'" :value="i.id">{{ i.name }} ({{ i.unit.name }})</el-option>
                    </el-select>
                  </td>
                  <td style="width:107px">
                    {{ newTransaction.detail[index].price_form | toCurrency }}
                  </td>
                  <td style="width:100px">
<!-- <<<<<<< HEAD
                    <input type="number" v-model="newTransaction.detail[index].quantity_form" @input="countSubtotal(newTransaction.detail[index].quantity_form,newTransaction.detail[index].discount_form,index)" @change="countSubtotal(newTransaction.detail[index].quantity_form,newTransaction.detail[index].discount_form,index)" />
                  </td>
                  <td style="width:106px">
                    <input type="number" max="100" min="0" v-model="newTransaction.detail[index].discount_form" @input="countSubtotal(newTransaction.detail[index].quantity_form,newTransaction.detail[index].discount_form,index)" @change="countSubtotal(newTransaction.detail[index].quantity_form,newTransaction.detail[index].discount_form,index)" />
======= -->
                    <el-input type="number" v-model="newTransaction.detail[index].quantity_form" @input="countSubtotal(newTransaction.detail[index].quantity_form,newTransaction.detail[index].discount_form,index)" @change="countSubtotal(newTransaction.detail[index].quantity_form,newTransaction.detail[index].discount_form,index)" />
                  </td>
                  <td style="width:106px">
                    <el-input type="number" v-model="newTransaction.detail[index].discount_form" @input="countSubtotal(newTransaction.detail[index].quantity_form,newTransaction.detail[index].discount_form,index)" @change="countSubtotal(newTransaction.detail[index].quantity_form,newTransaction.detail[index].discount_form,index)"  />
                  </td>
                  <td style="width:116px">{{ newTransaction.detail[index].subtotal_form | toCurrency }}</td>
                  <td>
                    <el-button type="danger" icon="el-icon-close" circle @click="spliceTransactionDetail(index)" />

                  </td>
                </tr>
              </tbody>
            </table>
            <hr>
          </div>
          <el-button type="addtable" @click="addNewItemForm">Add Item</el-button>

          <el-form-item class="total_price" label="Total Price" prop="total" style="float:right">
            <el-input v-show="false" v-model="newTransaction.total_form" class="total_price" />
            {{ newTransaction.total_form | toCurrency }}
          </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
          <el-button type="canceltransaksi" @click="dialogFormVisible = false">
            {{ $t('table.cancel') }}
          </el-button>
          <el-button v-if="transactionId <= 0" type="addtransaksi" @click="createTransaction()">
            {{ $t('table.confirm') }}
          </el-button>
          <el-button v-if="transactionId > 0" type="addtransaksi" @click="onUpdate()">
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
      itemList: null,
      total: 0,
      loading: true,
      downloading: false,
      transactionCreating: false,
      transactionId: 0,
      query: {
        page: 1,
        limit: 15,
        keyword: '',
        role: '',
      },
      queryVendor: {
        paginate: false,
      },
      queryItem: {
        paginate: false,
      },
      nonAdminRoles: ['editor', 'user', 'visitor'],
      newTransaction: {
        transaction_no: '',
        date_form:'',
        vendor_id: 0,
        total: 0,
        status: 0,
        detail: [],
      },
      status: [
        { label: 'Paid', value: 1 },
        { label: 'Unpaid', value: -1 },
      ],
      titleForm: '',
      transcationId: 0,
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
        created_at_form : [{ required: true, message: 'Date is required', trigger: 'blur' }],
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
    this.getItemList();
    if (checkPermission(['manage permission'])) {
      this.getPermissions();
    }
  },
  methods: {
    forceupdate(){
      this.$forceUpdate();
    },
    spliceTransactionDetail(index){
      this.newTransaction.detail.splice(index, 1);
    },
    addNewItemForm(){
      this.newTransaction.detail.push(
        {
          item_id_form: 0,
          quantity_form: 0,
          discount_form: 0,
          subtotal_form: 0,
        }
      );
      console.log(this.newTransaction.detail);
    },
    checkPermission,
    async getPermissions() {
      const { data } = await permissionResource.list({});
      const { all, menu, other } = this.classifyPermissions(data);
      this.permissions = all;
      this.menuPermissions = menu;
      this.otherPermissions = other;
    },
    async getItemList() {
      const { limit, page } = this.queryItem;
      this.loading = true;
      const { data, meta } = await itemResource.list(this.queryItem);
      this.itemList = data;
      this.total = meta.total;
      this.loading = false;
    },
    async getVendorList() {
      const { limit, page } = this.queryVendor;
      this.loading = true;
      const { data, meta } = await vendorResource.list(this.queryVendor);
      this.vendorList = data;
      this.total = meta.total;
      this.loading = false;
    },
    async getList() {
      const { limit, page } = this.query;
      this.loading = true;
      const { data, meta } = await transactionResource.list(this.query);
      this.list = data;
      console.log(this.list);

      this.list.forEach((element, index) => {
        element['index'] = (page - 1) * limit + index + 1;
      });
      this.total = meta.total;
      this.loading = false;
    },
    setItemPrice(index){
      const value = this.newTransaction.detail[index].item_id_form;
      const filterItem = this.itemList.filter(function(i){
        return i.id == value;
      });
      this.newTransaction.detail[index].price_form = filterItem[0].price;
      this.countSubtotal(this.newTransaction.detail[index].quantity_form, this.newTransaction.detail[index].discount_form, index);
    },
    countSubtotal(quantity, discount, index){
      let result = 0;
      if (discount) {
        result = (quantity * this.newTransaction.detail[index].price_form) - (discount / 100 * quantity * this.newTransaction.detail[index].price_form);
      } else {
        result = this.newTransaction.detail[index].price_form * quantity;
      }
      this.newTransaction.detail[index].subtotal_form = result;

      // countTOTAL
      let total = 0;
      this.newTransaction.detail.forEach(element => {
        total += element.subtotal_form;
      });
      console.log(total);
      this.newTransaction.total_form = total;
      // countTOTALEND
      console.log({ quantity, discount, result });
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
      this.titleForm = 'Edit Transaction';
      console.log(data);
      this.transactionId = data.id;
      this.newTransaction.transaction_no_form = data.transaction_no;
      this.newTransaction.vendor_id_form = data.vendor_id;
      this.newTransaction.total_form = data.total;
      this.newTransaction.status_form = data.status;
      this.newTransaction.date_form = data.created_at;

      console.log(this.newTransaction.status_form);

      const tmp = [];
      data.detail_transaction.forEach(function(element, i) {
        tmp.push(
          {
            'id': element.id,
            'transaction_id_form': element.transaction_id,
            'item_id_form': element.item_id,
            'price_form': element.item.price,
            'quantity_form': element.quantity,
            'discount_form': element.discount,
            'subtotal_form': element.subtotal,
            'status_form': element.status,
          }
        );
      });

      this.newTransaction.detail = tmp;
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
    createTransaction() {
      this.$refs['itemForm'].validate((valid) => {
        if (valid) {
          this.transactionCreating = true;
          transactionResource
            .store(this.newTransaction)
            .then(response => {
              this.$message({
                message: 'New Transaction ' + this.newTransaction.transaction_no + ' has been created successfully.',
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
    // handleUpdateStatus(id,status){
    //   this.transactionCreating = true;
    //   transactionResource
    //     .changeStatus(id, {'status_form' : status})
    //     .then(response => {
    //       this.$message({
    //         message: 'Transaction has been updated successfully',
    //         type: 'success',
    //         duration: 5 * 1000,
    //       });
    //       this.resetnewTransaction();
    //       this.dialogFormVisible = false;
    //       this.handleFilter();
    //     })
    //     .catch(error => {
    //       console.log(error);
    //     })
    //     .finally(() => {
    //       this.transactionCreating = false;
    //     });
    // },
    onUpdate() {
      console.log('update');

      this.$refs['itemForm'].validate((valid) => {
        if (valid) {
          this.transactionCreating = true;
          transactionResource
            .update(this.transactionId, this.newTransaction)
            .then(response => {
              this.$message({
                message: 'Transaction has been updated successfully',
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
        transaction_no: '',
        created_at:'',
        vendor_id: 0,
        total: 0,
        status: 0,
        detail: [],
      };
      this.transactionId = 0;
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
  width:850px;
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
    font-size:20x;
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
          font-size:16px;
      }
      .div_tabel{
        .transaksi_tabel_add{
          width:100%;
          border-spacing: 10px;
          border-collapse: separate;
           thead{
            color:#707070;
            font-size:16px;
            font-family: 'Ubuntu', sans-serif;
            font-weight:400;
            line-height:11px;
            text-align:left;
          }
          tbody{
           color:#707070;
           font-size:16px;
           font-family: 'Ubuntu', sans-serif;
           font-weight:400;
           line-height:11px;
           .el-select-dropdown{
             .el-scrollbar{
               .el-select-dropdown__wrap{
                 .el-select-dropdown__item {
                      font-size:16px;
                      padding: 0 15px;
                      position: relative;
                      white-space: nowrap;
                      overflow: hidden;
                      text-overflow: ellipsis;
                      color: #606266;
                      height: 34px;
                      line-height: 34px;
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
          font-size:20x;
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
             font-size:16px;
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
  padding: 19px;
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
            font-size:20x;
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
