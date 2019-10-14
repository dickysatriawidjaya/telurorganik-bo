<template>
  <div>
    <h1>Transaction BELUM LUNAS</h1>

    <div class="filter-container">
      <el-select v-model="query.vendor" placeholder="Vendor" clearable style="width: 150px" class="filter-item" @change="handleFilter">
        <el-option v-for="v in vendorList" :key="v.id" :label="v.name" :value="v.id">
          <span style="float: left">{{ v.name }}</span>
          <span style="float: right; color: #8492a6; font-size: 13px">{{ (v.pic_name)?v.pic_name:"-" }}</span>
        </el-option>
      </el-select>

      <router-link v-if="query.vendor" target="_blank" :to="{ path: '/pdf/transactionBELUMLUNAS/' + query.vendor }">
        <el-button type="primary">
          PRINT
        </el-button>
      </router-link>

      <router-link v-else target="_blank" :to="{ path: '/pdf/transactionBELUMLUNAS/' + 0 }">
        <el-button type="primary">
          PRINT
        </el-button>
      </router-link>

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

      <el-table-column class-name="status-col" label="Created Date" width="110" prop="created_at" sortable>
        <template slot-scope="scope">
          <span>{{ scope.row.created_at | moment("DD MMMM  YYYY") }}</span>
        </template>
      </el-table-column>

      <el-table-column class-name="status-col" label="Status" width="110" prop="status" sortable>
        <template slot-scope="scope">
          <el-tag v-if="scope.row.status == 1" type="success">
            LUNAS
          </el-tag>
          <el-tag v-if="scope.row.status == -1" type="warning">
            BELUM LUNAS
          </el-tag>
        </template>
      </el-table-column>

      <el-table-column align="center" label="Actions">
        <template slot-scope="scope">
          <router-link :to="'/administrator/users/edit/'+scope.row.id">
            <el-button type="primary" size="small" icon="el-icon-edit">
              Detail
            </el-button>
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
      query: {
        page: 1,
        limit: 15,
        keyword: '',
        role: '',
        status: -1,
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
