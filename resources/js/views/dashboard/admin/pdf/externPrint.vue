<template>
  <div class="print">
    <div>
      Vendor : <span v-if="query.vendor">{{ vendor.name }}</span>
      <span v-else> All </span>

      <div style="float:right;">
        <span v-if="query.start_date"> {{ query.start_date | moment("DD/MMM/YYYY") }} </span>
        <span v-else> All </span>
        -
        <span v-if="query.end_date"> {{ query.end_date | moment("DD/MMM/YYYY") }} </span>
        <span v-else> All </span>
        (<span v-if="query.status == 1"> Paid </span>
        <span v-else-if="query.status == -1"> Unpaid </span>
        <span v-else> All </span>)
      </div>
    </div>

    <table class="cust-table" style="width:100%" border="1">
      <col>
      <colgroup span="2"></colgroup>
      <thead>
        <tr>
          <th rowspan="2" width="28">No</th>
          <th rowspan="2" width="55">Date</th>
          <th rowspan="2" width="150">Vendor</th>
          <th rowspan="2" width="85">Trans.ID</th>
          <th colspan="6" width="320">Item</th>
          <th rowspan="2" width="100">Total</th>
        </tr>
        <tr>
          <th width="74">Barang</th>
          <th width="55">Unit</th>
          <th width="42">Qty</th>
          <th width="48">Disc</th>
          <th width="59">Retur</th>
          <th width="100" style="text-align:right;padding-right:4px;">Sub Total</th>
        </tr>

      </thead>
      <tbody>
        <tr v-for="(t,index) in list" :key="index">
          <td width="28" style="text-align:center;">{{ index + 1 }}</td>
          <td width="55">{{ t.transaction_date | moment("DD/MMM/YY") }}</td>
          <td width="150">{{ t.vendor.name }}</td>
          <td width="85">{{ t.transaction_no }}</td>
          <td class="no-padding" colspan="6" width="320">
            <tbody>
              <tr v-for="(d,index_detail) in t.detail_transaction" :key="index_detail" v-if="t.detail_transaction.length > 0" width="320">
                <td width="74">{{ d.item.name }}</td>
                <td width="55">{{ d.item.unit.name }}</td>
                <td style="text-align:right;padding-right:4px;" width="42">{{ d.quantity }}</td>
                <td style="text-align:right;padding-right:4px;" width="48">{{ d.discount }} %</td>
                <td style="text-align:right;padding-right:4px;" width="59">{{ d.retur }}</td>
                <td width="100" style="text-align:right;padding-right:4px;">{{ d.subtotal | toCurrency }}</td>
              </tr>
            </tbody>
          </td>
          <td width="100" style="text-align:right;padding-right:4px;">{{ t.total | toCurrency }}</td>
        </tr>
      </tbody>

    </table>

    <!-- <el-table v-loading="loading" :data="list" border fit style="width: 100%">
      <el-table-column align="center" label="No." prop="index" width="28" style="">
        <template slot-scope="scope">
          <span>{{ scope.row.index }}</span>
        </template>
      </el-table-column>
      <el-table-column align="left" class-name="status-col" label="Date" width="55" prop="created_at">
        <template slot-scope="scope">
          <span>{{ scope.row.transaction_date | moment("DD/MMM/YY") }}</span>
        </template>
      </el-table-column>
      <el-table-column align="left" label="Vendor" prop="vendor">
        <template slot-scope="scope">
          <span>{{ scope.row.vendor.name }}</span>
        </template>
      </el-table-column>
      <el-table-column align="left" label="Trans.ID" width="100" prop="transaction_no">
        <template slot-scope="scope">
          <span>{{ scope.row.transaction_no }}</span>
        </template>
      </el-table-column>
      <el-table-column align="left" prop="Item" width="320">
        <template slot-scope="scope">
          <div>
            <tr>
              <th width="105">Barang</th>
              <th width="50">Unit</th>
              <th width="50">Qty</th>
              <th width="65">Disc</th>
              <th width="90" style="text-align:right;">Sub Total</th>
            </tr>
          </div>
          <tr v-for="(d,index_detail) in scope.row.detail_transaction">
            <td width="105">{{ d.item.name }}</td>
            <td width="50">{{ d.item.unit.name }}</td>
            <td width="50">{{ d.quantity }}</td>
            <td width="65">{{ d.discount }} %</td>
            <td width="90" style="text-align:right;">{{ d.subtotal | toCurrency }}</td>
          </tr>
        </template>
      </el-table-column>
      <el-table-column align="right" label="Total" width="150" prop="total">
        <template slot-scope="scope">
          <span>{{ scope.row.total | toCurrency }}</span>
        </template>
      </el-table-column>
    </el-table> -->
    <div style="float:right; font-weight: 500; margin-top: 4px; position: relative; right: 4px;color: #707070;"><span style="position: relative;right: 25px;">Grand Total</span> {{ grand_total | toCurrency }}</div>
    <el-button class="printButton" icon="el-icon-printer" onclick="window.print()">Print page</el-button>
  </div>
</template>

<script>
import TransactionResource from '@/api/transaction';
import VendorResource from '@/api/vendor';

const vendorResource = new VendorResource();
const transactionResource = new TransactionResource();

import Cookies from 'js-cookie';

export default {
  data() {
    return {
      grand_total: 0,
      article: '',
      fullscreenLoading: true,
      vendor_id: 0,
      list: null,
      loading: true,
      vendorList: null,
      queryVendor: {
        paginate: false,
      },
      vendor: null,
      query: {
        print : true,
        page: 1,
        limit: 9999,
        keyword: '',
        role: Cookies.get('Role'),
        status: null,
        vendor: null,
        start_date: '',
        end_date: '',
      },
      total: 0,
    };
  },
  mounted() {
    if (this.$route.query.vendor == 'null'){
      this.query.vendor = 0;
    } else {
      this.query.vendor = this.$route.query.vendor;
    }

    if (this.$route.query.start_date == 'null'){
      this.query.start_date = null;
    } else {
      this.query.start_date = this.$route.query.start_date;
    }

    if (this.$route.query.end_date == 'null'){
      this.query.end_date = null;
    } else {
      this.query.end_date = this.$route.query.end_date;
    }

    if (this.$route.query.status == 'null'){
      this.query.status = null;
    } else {
      this.query.status = this.$route.query.status;
    }

    this.fetchData();
    this.getVendor();
  },
  methods: {
    async getVendor() {
      const { limit, page } = { 'id': this.query.vendor };
      this.loading = true;
      const { data, meta } = await vendorResource.list({ 'id': this.query.vendor });
      this.vendor = data[0];
      console.log(this.vendor);
      this.total = meta.total;
      this.loading = false;
    },
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
      let sum = 0;

      this.list.forEach((element, index) => {
        element['index'] = (page - 1) * limit + index + 1;
        sum += parseFloat(element.total);
      });
      this.grand_total = sum;
      this.total = meta.total;
      this.loading = false;
      // setTimeout(() => {
      //
      //   this.$nextTick(() => {
      //     window.print();
      //   });
      // }, 5000);
    },
  },
};
</script>

<style rel="stylesheet/scss" lang="scss">
  .printButton {
    position: absolute;
    right: 30%;
    top: 3%;
    border: 1px solid #000;
    background: #000;
    color: #fff;
    padding: 5px 10px;
  }
  .print{
    width:793px;
    color: #707070;
    .cust-table{
        color: #707070 !important;
        font-size:10px;
        font-family: 'Ubuntu', sans-serif;
        font-weight: 400;
        line-height:11px;
        padding:0px !important;
        border-collapse: collapse;
        border: 1px solid #dfe6ec;
        thead {
          tr {
            th {
              border: 1px solid #dfe6ec;
              background-color: #f4f4f4 !important;
            }
          }
        }
        tbody {
          tr {
            td {
              border: 1px solid #dfe6ec;
              padding: 0 2px;
              td {
                border-right: 1px solid #dfe6ec;
                border-bottom: none;
                border-top: none;
                border-left: none;
                padding: 2px;
                &:last-child {
                  border-right: 0;
                }
              }
            }
            .no-padding {
              padding: 0;
            }
          }
        }
    }
    @media print {
      .printButton {
        display: none;
      }
      .cust-table{
          color: #707070 !important;
          font-size:10px;
          font-family: 'Ubuntu', sans-serif;
          font-weight: 400;
          line-height:11px;
          padding:0px !important;
          border-collapse: collapse;
          border: 1px solid #000;
          thead {
            tr {
              th {
                border: 1px solid #000;
                background-color: #f4f4f4 !important;
              }
            }
          }
          tbody {
            tr {
              td {
                border: 1px solid #000;
                padding: 0 2px;
                td {
                  border-right: 1px solid #000;
                  border-bottom: none;
                  border-top: none;
                  border-left: none;
                  padding: 2px;
                  &:last-child {
                    border-right: 0;
                  }
                }
              }
              .no-padding {
                padding: 0;
              }
            }
          }
      }
      // table {
      //     border: solid #000 !important;
      //     border-width: 1px 0 0 1px !important;
      //     border-bottom: 1px solid #000 !important;
      //     border-right: 1px solid #000 !important;
      // }
      // tr, td {
      //     border: solid #000 !important;
      //     border-width: 0 1px 1px 0 !important;
      // }
      // tr {
      //   &:first-child {
      //     display:none;
      //   }
      // }
    }
  }
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
.el-table__row {
  td {
    padding: 2px 0 !important;
  }
  .el-table_1_column_5 {
    padding: 0 !important;
    .cell {
      padding: 0;
      tr {
        &:first-child {
          display:none;
        }
        &:last-child {
          td {
            border-bottom: none !important;
          }
        }
        th {
          padding: 0;
          background: #D3D3D3;
          color: #fff;
        }
        th,td {
          border-right: none !important;
        }
      }
    }
  }
  &:first-child {
    .el-table_1_column_5 {
      padding: 0 !important;
      .cell {
        padding: 0;
        tr {
          &:first-child {
            display:table-row !important;
          }
          th {
            padding: 0;
            background: #D3D3D3;
            color: #fff;
          }
          th,td {
            border-right: none !important;
          }
        }
      }
    }
  }
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
