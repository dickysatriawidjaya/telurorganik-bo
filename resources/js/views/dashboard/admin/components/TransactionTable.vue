<template>
  <div class="table-db-container">
    <div class="filter-container">
      <el-row>
        <el-col :span="8">
          <h1 class="table_paid">Paid</h1>
        </el-col>
        <el-col :span="16">
          <div class="button_print">
            <router-link target="_blank" :to="{ path: '/pdf/transactionLUNAS/?vendor=' + query.vendor +'&month='+ query.month }">
              <el-button type="print" icon="el-icon-printer" style="font-size:15px">
                PRINT
              </el-button>
            </router-link>
          </div>
        </el-col>
      </el-row>
      <el-row>
        <el-col :span="2">
          <h3 class="text_normal">
            Vendor :
          </h3>
        </el-col>
        <el-col :span="16">
          <el-select v-model="query.vendor" placeholder="Vendor Filter" clearable style="width: 235px; margin-top:13px" class="filter-item" @change="handleFilter">
            <el-option v-for="v in vendorList" :key="v.id" :label="v.name" :value="v.id">
              <span style="float: left">{{ v.name }}</span>
              <span style="float: right; color: #8492a6; font-size: 13px">{{ (v.pic_name)?v.pic_name:"-" }}</span>
            </el-option>
          </el-select>
        </el-col>
      </el-row>
      <el-row>
        <el-col :span="3">
          <h3 class="text_normal">
            Date Month:
          </h3>
        </el-col>
        <el-col :span="16">
          <el-select v-model="query.month" placeholder="Date Month Filter" clearable style="width: 235px; margin-top:13px" class="filter-item" @change="handleFilter">
            <el-option v-for="d in dateMonth" :key="d.value" :label="d.name" :value="d.value">
              <span style="float: left">{{ d.name }}</span>
            </el-option>
          </el-select>
        </el-col>
      </el-row>
    </div>

    <el-table v-loading="loading" :data="list" border fit highlight-current-row style="width: 100%">
      <el-table-column align="left" label="No." prop="index" width="60">
        <template slot-scope="scope">
          <span>{{ scope.row.index }}</span>
        </template>
      </el-table-column>
      <el-table-column align="left" class-name="status-col" label="Date" prop="created_at" width="120" sortable>
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
      <el-table-column align="left" class-name="status-col" label="Status" prop="status" width="100">
        <template slot-scope="scope">
          <span v-if="scope.row.status == 1" style="color:#46A2FD">
            Paid
          </span>
          <span v-if="scope.row.status == -1" style="color:##FD4646">
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
  </div>
</template>

  <style rel="stylesheet/scss" lang="scss">
    .table-db-container{
      background:#FFFFFF;
      padding:16px;
      width:100%;
      border-radius: 10px;
      .text_normal{
        color: #707070;
        font-weight: 500;
        font-size:16px;
        font-family: 'Ubuntu', sans-serif;
        font-weight: 400;
        line-height:29px;
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
        top: -24px;
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
      line-height:34px;
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
        font-weight: 400;
        line-height:11px;
        border: 1px solid #707070;
        padding: 7px 12px;
        margin-top:20px;
        border-radius:13px;
    }
  </style>
<script>
import { fetchList } from '@/api/order';
import TransactionResource from '@/api/transaction';
import VendorResource from '@/api/vendor';

const transactionResource = new TransactionResource();
const vendorResource = new VendorResource();

export default {
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
      dateMonth:[
        {value : 1,name:"January"},
        {value : 2,name:"February"},
        {value : 3,name:"March"},
        {value : 4,name:"April"},
        {value : 5,name:"Mei"},
        {value : 6,name:"June"},
        {value : 7,name:"July"},
        {value : 8,name:"August"},
        {value : 9,name:"September"},
        {value : 10,name:"October"},
        {value : 11,name:"November"},
        {value : 12,name:"Desember"},
      ],
      query: {
        page: 1,
        limit: 15,
        keyword: '',
        role: '',
        status: 1,
        month:moment().month()+1,
        vendor: null,
      },
      total: 0,
    };
  },
  created() {
    this.getList();
    this.getVendorList();
    this.query.month = moment().month()+1
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
