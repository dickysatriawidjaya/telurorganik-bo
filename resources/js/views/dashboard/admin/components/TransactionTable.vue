<template>
  <div class="table-db-container">
    <div class="filter-container">
      <el-row>
        <el-col :span="8">
          <h1 class="table_paid">Transactions</h1>
        </el-col>
        <el-col :span="16">
          <div class="button_print">
            <router-link target="_blank" :to="{ path: '/pdf/internPrint/?vendor=' + query.vendor +'&start_date='+ query.start_date +'&end_date='+ query.end_date +'&status='+ query.status}">
              <el-button type="print" icon="el-icon-printer">
                Intern Print
              </el-button>
            </router-link>
            <br>
            <router-link target="_blank" :to="{ path: '/pdf/externPrint/?vendor=' + query.vendor +'&start_date='+ query.start_date +'&end_date='+ query.end_date +'&status='+ query.status}">
              <el-button type="print" icon="el-icon-printer">
                Extern Print
              </el-button>
            </router-link>
          </div>
        </el-col>
      </el-row>
      <el-row>
        <el-col class="element-vendor">
          <h3 class="text_normal">
            Vendor :
          </h3>
          <el-select v-model="query.vendor" placeholder="Vendor Filter" clearable class="filter-item" @change="handleFilter">
            <el-option v-for="v in vendorList" :key="v.id" :label="v.name" :value="v.id">
              <span style="float: left">{{ v.name }}</span>
              <span style="float: right; color: #8492a6; font-size: 13px">{{ (v.pic_name)?v.pic_name:"-" }}</span>
            </el-option>
          </el-select>
        </el-col>
        <el-col :span="4">
          <h3 class="text_normal">
            Status :
          </h3>
          <el-select v-model="query.status" :placeholder="$t('table.status')" clearable style="width: 100px" class="filter-item tabel_filter" @change="handleFilter">
            <el-option key="1" label="Paid" class="paid" style="font-size:16px;font-weight:500;" value="1" />
            <el-option key="-1" label="Unpaid" class="unpaid" style="font-size:16px;font-weight:500;" value="-1" />
          </el-select>
        </el-col>
      </el-row>
      <el-row>
        <el-col class="element-datepicker">
          <h3 class="text_normal">
            Start Date:
          </h3>
          <datepicker v-model="query.start_date" class="add-separator" clear-button="true" name="uniquename" @selected="handleFilter" />
        </el-col>
        <el-col class="element-datepicker">
          <h3 class="text_normal">
            End Date:
          </h3>
          <datepicker v-model="query.end_date" clear-button="true" name="uniquename" @selected="handleFilter" />
        </el-col>
      </el-row>
    </div>
    <el-table v-loading="loading" :data="list" border fit highlight-current-row style="width: 100%">
      <el-table-column align="left" label="No." prop="index" width="60">
        <template slot-scope="scope">
          <span>{{ scope.row.index }}</span>
        </template>
      </el-table-column>
      <el-table-column align="left" class-name="status-col" label="Trans Date" prop="created_at" width="130" sortable>
        <template slot-scope="scope">
          <span>{{ scope.row.transaction_date | moment("DD/MMM/YY") }}</span>
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
      <el-table-column align="left" label="Total" prop="total" width="200" sortable>
        <template slot-scope="scope">
          <span>{{ scope.row.total | toCurrency }}</span>
        </template>
      </el-table-column>
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
      <el-table-column align="center" label="Actions" width="100">
        <template slot-scope="scope">
          <router-link :to="'/transaction/detail/'+scope.row.id">
            <el-button v-permission="['manage user']" size="medium" icon="el-icon-document" circle />
          </router-link>
        </template>
      </el-table-column>
    </el-table>
    <pagination v-show="total>0" :total="total" :page.sync="query.page" :limit.sync="query.limit" class="is-right" @pagination="getList" />
  </div>
</template>

<style rel="stylesheet/scss" lang="scss">
  .filter-container {
    .el-button {
      font-size: 16px;
    }
    .text_normal {
      font-weight: 500 !important;
      color: #707070;
    }
    .vdp-datepicker {
      width: 100%;
      &.add-separator::after {
        content: "-";
        font-size: 16px;
        position: absolute;
        top: 7px;
        right: -17px;
      }
      input {
        width: 100%;
        height: 35px;
        line-height: 35px;
        background: #F4F4F4;
        color: #707070;
        font-weight: 500;
        font-size: 16px;
        font-family: 'Ubuntu', sans-serif;
        font-weight: 400;
        line-height: 11px;
        border-radius: 4px;
        border: 1px solid #DCDFE6;
        padding: 0 20px 0 15px;
      }
      .vdp-datepicker__clear-button {
        position: absolute;
        right: 8px;
        top: 6px;
        font-size: 18px;
        span {
          font-style: normal;
        }
      }
    }
  }
  .table-db-container{
    background:#FFFFFF;
    padding:16px;
    width:100%;
    border-radius: 10px;
    .text_normal{
      color: #707070;
      font-weight: 500;
      font-size: 16px;
      font-family: 'Ubuntu', sans-serif;
      font-weight: 400;
      line-height: 29px;
      padding: 0;
      margin: 0;
    }
    .el-input--medium .el-input__inner {
      height: 35px;
      line-height: 35px;
      color: #707070;
      font-weight: 500;
      font-size:16px;
      font-family: 'Ubuntu', sans-serif;
      font-weight: 400;
      line-height:11px;
    }
    .el-input__suffix {
        position: absolute;
        height: 50%;
        right: 5px;
        top: 19px;
        text-align: center;
        color: #C0C4CC;
        transition: all .3s;
        pointer-events: none;
    }
    .el-select .el-input .el-select__caret.is-reverse {
      transform: rotateZ(0deg);
      top: -19px;
      left: -26px;
      position: absolute;
    }
    .el-button--medium.is-circle {
      padding: 8px;
      border: none;
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
          font-size: 10px;
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
  .table_paid{
    color:#46A2FD;
    font-size:33px;
    font-family: 'Abel', sans-serif;
    font-weight: 400;
    line-height:40px;
  }
  .table_unpaid{
    color:#FD4646;
    font-size:33px;
    font-family: 'Abel', sans-serif;
    font-weight: 400;
    line-height:34px;
  }
  .button_print{
    text-align: right;
  }
  .el-button--print {
      color: #707070;
      background-color: #FFFFFF;
      font-size:16px;
      font-family: 'Ubuntu', sans-serif;
      font-weight: 500;
      line-height:11px;
      width:150px;
      border: 1px solid #707070;
      padding: 9px 11px;
      margin-top:6px;
      border-radius:19px;
  }
  .element-datepicker {
    width: 135px;
    margin-right: 30px;
  }
  .element-vendor {
    width: 345px;
    margin-right: 14px;
    .el-select {
      width: 100%;
    }
  }
</style>

<script>
import Datepicker from 'vuejs-datepicker';

import { fetchList } from '@/api/order';
import TransactionResource from '@/api/transaction';
import VendorResource from '@/api/vendor';

const transactionResource = new TransactionResource();
const vendorResource = new VendorResource();

import Cookies from 'js-cookie';

export default {
  components: { Datepicker },
  filters: {
    statusFilter(status) {
      const statusMap = {
        success: 'success',
        pending: 'danger',
      };
      return statusMap[status];
    },
    orderNoFilter(str) {
      return str;
    },
  },
  data() {
    return {
      list: null,
      loading: true,
      vendorList: null,
      queryVendor: {
        paginate: false,
      },
      dateMonth: [
        { value: 1, name: 'January' },
        { value: 2, name: 'February' },
        { value: 3, name: 'March' },
        { value: 4, name: 'April' },
        { value: 5, name: 'Mei' },
        { value: 6, name: 'June' },
        { value: 7, name: 'July' },
        { value: 8, name: 'August' },
        { value: 9, name: 'September' },
        { value: 10, name: 'October' },
        { value: 11, name: 'November' },
        { value: 12, name: 'Desember' },
      ],
      query: {
        page: 1,
        limit: 9999,
        keyword: '',
        role: Cookies.get('Role'),
        status: null,
        vendor: null,
        start_date: moment().startOf('month').format('YYYY-MM-DD hh:mm'),
        end_date: moment().endOf('month').format('YYYY-MM-DD hh:mm'),
      },
      status: [
        { label: 'Paid', value: 1 },
        { label: 'Unpaid', value: -1 },
      ],
      total: 0,
    };
  },
  created() {
    this.getList();
    this.getVendorList();
  },
  mounted: function() {
    this.$nextTick(function() {
      window.setInterval(() => {
        this.getList();
      }, 60000);
    });
  },
  methods: {
    handleFilter() {
      this.query.page = 1;
      this.getList();
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

      this.list.forEach((element, index) => {
        element['index'] = (page - 1) * limit + index + 1;
      });
      this.total = meta.total;
      this.loading = false;
    },
  },
};
</script>
