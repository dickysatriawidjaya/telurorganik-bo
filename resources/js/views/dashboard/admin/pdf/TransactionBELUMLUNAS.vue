<template>
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
</template>

<script>
import TransactionResource from '@/api/transaction';

const transactionResource = new TransactionResource();
export default {
  data() {
    return {
      article: '',
      fullscreenLoading: true,
      vendor_id: 0,
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
  mounted() {
    if(this.$route.query.vendor == "null"){
      this.query.vendor = 0;
    }else{
      this.query.vendor = this.$route.query.vendor;
    }
    
    this.query.month = this.$route.query.month;

    this.fetchData();
  },
  methods: {
    // async getList() {
    //   const { limit, page } = this.query;
    //   this.loading = true;
    //   const { data, meta } = await transactionResource.list(this.query);
    //   this.list = data;

    //   this.list.forEach((element, index) => {
    //     element['index'] = (page - 1) * limit + index + 1;
    //   });
    //   this.total = meta.total;
    //   this.loading = false;
    // },
    async fetchData() {
      const { limit, page } = this.query;
      this.loading = true;
      const { data, meta } = await transactionResource.list(this.query);
      this.list = data;

      this.list.forEach((element, index) => {
        element['index'] = (page - 1) * limit + index + 1;
      });
      this.total = meta.total;

      setTimeout(() => {
        this.loading = false;
        this.$nextTick(() => {
          window.print();
        });
      }, 5000);
    },
  },
};
</script>

<style rel="stylesheet/scss" lang="scss">
@mixin clearfix {
  &:before {
    display: table;
    content: '';
    clear: both;
  }

  &:after {
    display: table;
    content: '';
    clear: both;
  }
}

.main-article {
  padding: 20px;
  margin: 0 auto;
  display: block;
  width: 740px;
  background: #fff;
}

.article__heading {
  position: relative;
  padding: 0 0 20px;
  overflow: hidden;
}

.article__heading__title {
  display: block;
  display: -webkit-box;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  word-wrap: break-word;
  overflow-wrap: break-word;
  font-size: 32px;
  line-height: 48px;
  font-weight: 600;
  color: #333;
  overflow: hidden;
}

.node-article-content {
  margin: 20px 0 0;
  @include clearfix;
  font-size: 16px;
  color: #333;
  letter-spacing: 0.5px;
  line-height: 28px;
  margin-bottom: 30px;
  font-family: medium-content-serif-font, Georgia, Cambria, 'Times New Roman',
    Times, serif;

  & > :last-child {
    margin-bottom: 0;
  }

  b,
  strong {
    font-weight: inherit;
    font-weight: bolder;
  }

  img {
    max-width: 100%;
    display: block;
    margin: 0 auto;
  }

  p {
    font-weight: 400;
    font-style: normal;
    font-size: 21px;
    line-height: 1.58;
    letter-spacing: -0.003em;
  }

  ul {
    margin-bottom: 30px;
  }

  li {
    --x-height-multiplier: 0.375;
    --baseline-multiplier: 0.17;

    letter-spacing: 0.01rem;
    font-weight: 400;
    font-style: normal;
    font-size: 21px;
    line-height: 1.58;
    letter-spacing: -0.003em;
    margin-left: 30px;
    margin-bottom: 14px;
  }

  a {
    text-decoration: none;
    background-repeat: repeat-x;
    background-image: linear-gradient(
      to right,
      rgba(0, 0, 0, 0.84) 100%,
      rgba(0, 0, 0, 0) 0
    );
    background-size: 1px 1px;
    background-position: 0 calc(1em + 1px);
    padding: 0 6px;
  }

  code {
    background: rgba(0, 0, 0, 0.05);
    padding: 3px 4px;
    margin: 0 2px;
    font-size: 16px;
    display: inline-block;
  }

  img {
    border: 0;
  }

  /* Resolve images jagged in IE6-7*/
  img {
    -ms-interpolation-mode: bicubic;
  }

  blockquote {
    --x-height-multiplier: 0.375;
    --baseline-multiplier: 0.17;
    font-family: medium-content-serif-font, Georgia, Cambria, 'Times New Roman',
      Times, serif;
    letter-spacing: 0.01rem;
    font-weight: 400;
    font-style: italic;
    font-size: 21px;
    line-height: 1.58;
    letter-spacing: -0.003em;
    border-left: 3px solid rgba(0, 0, 0, 0.84);
    padding-left: 20px;
    margin-left: -23px;
    padding-bottom: 2px;
  }

  a {
    text-decoration: none;
  }

  h2,
  h3,
  h4 {
    font-size: 34px;
    line-height: 1.15;
    letter-spacing: -0.015em;
    margin: 53px 0 0;
  }

  h4 {
    font-size: 26px;
  }
}
</style>
