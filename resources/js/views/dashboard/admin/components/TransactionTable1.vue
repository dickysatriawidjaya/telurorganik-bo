<template>
  <div class="table-db-container">
    <div class="filter-container">
      <el-row>
        <el-col :span="8">
          <h1 class="table_unpaid">Unpaid</h1>
        </el-col>
        <el-col :span="16">
          <div class="button_print">
            <router-link target="_blank" :to="{ path: '/pdf/transactionBELUMLUNAS/?vendor=' + query.vendor +'&month='+ query.month }">
              <el-button type="print" icon="el-icon-printer" style="font-size:15px">
                PRINT
              </el-button>
            </router-link>

            <!-- <router-link v-if="query.vendor" target="_blank" :to="{ path: '/pdf/transactionBELUMLUNAS/' + query.vendor }">
              <el-button type="print" icon="el-icon-printer" style="font-size:15px">
                PRINT
              </el-button>
            </router-link>

            <router-link v-else target="_blank" :to="{ path: '/pdf/transactionBELUMLUNAS/' + 0 }">
              <el-button type="print" icon="el-icon-printer" style="font-size:15px">
                PRINT
              </el-button>
            </router-link> -->
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
          <el-select v-model="query.vendor" placeholder="Vendor" clearable style="width: 235px;margin-top: 13px;" class="filter-item" @change="handleFilter">
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
      <el-table-column align="left" class-name="status-col" label="Date" width="120" prop="created_at" sortable>
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
      <el-table-column align="left" label="Tran.ID" prop="transaction_no" sortable width="140">
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
          <span v-if="scope.row.status == -1" style="color:#FD4646 !important">
            Unpaid
          </span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="Actions" width="100">
        <template slot-scope="scope">
          <router-link :to="'/administrator/users/edit/'+scope.row.id">
            <el-button type="" size="medium" icon="el-icon-edit" circle />
          </router-link>
        </template>
      </el-table-column>
    </el-table>
    <!-- <el-table
      v-loading="loading"
      :data="list"
      style="width: 100%;padding-top: 15px;"
    >
      <el-table-column label="Order #" min-width="200">
        <template slot-scope="scope">
          {{ scope.row && scope.row.order_no | orderNoFilter }}
        </template>
      </el-table-column>
      <el-table-column label="Price" width="195" align="center">
        <template slot-scope="scope">
          ¥{{ scope.row && scope.row.price | toThousandFilter }}
        </template>
      </el-table-column>
      <el-table-column label="Status" width="100" align="center">
        <template slot-scope="scope">
          <el-tag :type="scope.row && scope.row.status | statusFilter">
            {{ scope.row && scope.row.status }}
          </el-tag>
        </template>
      </el-table-column>
    </el-table> -->
  </div>
</template>

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
        status: -1,
        month:moment().month()+1,
        vendor: null,
      },
      total: 0,
    };
  },
  created() {
    this.getList();
    this.getVendorList();
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
