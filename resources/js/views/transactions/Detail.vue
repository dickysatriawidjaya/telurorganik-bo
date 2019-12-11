<template>
  <div class="app-container">
    <div class="detail_trans">
      <h1 class="title_detail">Transaction Detail</h1>
      <el-row>
        <el-col :span="12">
          <el-row>
            <el-col :span="8">
              <h3 class="text_normal">
                Transasction No.
              </h3>
            </el-col>
            <el-col :span="12">
              <h3 class="text_detail">
                {{ transaction.transaction_no }}
              </h3>
            </el-col>
          </el-row>
        </el-col>
        <el-col :span="12">
          <el-row>
            <el-col :span="8">
              <h3 class="text_normal">
                Status
              </h3>
            </el-col>
            <el-col :span="12">
              <!-- <h3 class="text_detail">
                    paid
                  </h3> -->
              <h3 v-if="transaction.status == 1" class="text_detail" style="color:#46A2FD">
                Paid
              </h3>
              <h3 v-if="transaction.status == -1" class="text_detail" style="color:#FD4646 !important">
                Unpaid
              </h3>
            </el-col>
          </el-row>
        </el-col>
      </el-row>
      <el-row>
        <el-col :span="12">
          <el-row>
            <el-col :span="8">
              <h3 class="text_normal">
                Vendor
              </h3>
            </el-col>
            <el-col :span="12">
              <h3 class="text_detail">
                {{ transaction.vendor.name }} ( {{ transaction.vendor.pic_name }} )
              </h3>
            </el-col>
          </el-row>
        </el-col>
        <el-col :span="12">
          <el-row>
            <el-col :span="8">
              <h3 class="text_normal">
                Transaction Date
              </h3>
            </el-col>
            <el-col :span="12">
              <h3 class="text_detail">
                {{ transaction.created_at | moment("DD-MMM-YYYY") }}
              </h3>
            </el-col>
          </el-row>
        </el-col>
      </el-row>
      <el-row>
        <el-col :span="12">
          <el-row>
            <el-col :span="8">
              <h3 class="text_normal">
                Phone
              </h3>
            </el-col>
            <el-col :span="12">
              <h3 class="text_detail">
                {{ transaction.vendor.phone }}
              </h3>
            </el-col>
          </el-row>
        </el-col>
        <el-col :span="12" />
      </el-row>
      <el-table v-loading="loading" :data="transaction.detail_transaction" border fit highlight-current-row style="width: 100%">
        <el-table-column align="left" label="No." prop="index" width="60">
          <template slot-scope="scope">
            <span>{{ scope.row.index }}</span>
          </template>
        </el-table-column>
        <el-table-column align="left" class-name="status-col" label="Date" prop="created_at" width="120" sortable>
          <template slot-scope="scope">
            <span>{{ scope.row.created_at | moment("DD-MMM-YYYY") }}</span>
          </template>
        </el-table-column>
        <el-table-column align="left" label="Item" prop="item" sortable>
          <template slot-scope="scope">
            <span>{{ scope.row.item.name }}</span>
          </template>
        </el-table-column>
        <el-table-column align="left" label="Price" prop="price" sortable>
          <template slot-scope="scope">
            <span>{{ scope.row.item.price | toCurrency }}</span>
          </template>
        </el-table-column>
        <el-table-column align="left" label="Qty" prop="quantity" sortable>
          <template slot-scope="scope">
            <span>{{ scope.row.quantity }}</span>
          </template>
        </el-table-column>
        <el-table-column align="left" label="Discount" prop="discount" sortable>
          <template slot-scope="scope">
            <span>{{ scope.row.discount }}</span>
          </template>
        </el-table-column>
        <el-table-column align="left" label="Total" prop="total" sortable>
          <template slot-scope="scope">
            <span>{{ scope.row.subtotal | toCurrency }}</span>
          </template>
        </el-table-column>
      </el-table>
      <el-row>
        <el-col :span="12" style="margin-left: 80%">
          <el-row>
            <el-col :span="5">
              <h3 class="text_normal">
                Total Price
              </h3>
            </el-col>
            <el-col :span="8">
              <h3 class="text_normal">
                {{ transaction.total | toCurrency }}
              </h3>
            </el-col>
          </el-row>
        </el-col>
      </el-row>
    </div>
  </div>
</template>
<style lang="scss">
.detail_trans{
      background:#FFFFFF;
      padding:16px;
      width:800px;
      border-radius: 10px;
      .vdp-datepicker {
        input {
          line-height: 35px;
          color: #707070;
          font-weight: 500;
          font-size: 16px;
          font-family: 'Ubuntu', sans-serif;
          font-weight: 400;
          line-height: 11px;
          border-radius: 4px;
          border: 1px solid #DCDFE6;
          padding: 0 30px 0 15px;
        }
      }
      .title_detail{
        color: #707070;
        font-weight: 500;
        font-size:33px;
        font-family: 'Ubuntu', sans-serif;
        font-weight: 400;
        line-height:29px;
      }
      .text_normal{
        color: #707070;
        font-weight: 500;
        font-size:16px;
        font-family: 'Ubuntu', sans-serif;
        font-weight: 500;
        line-height:29px;
      }
      .text_detail{
        color: #707070;
        font-weight: 500;
        font-size:16px;
        font-family: 'Ubuntu', sans-serif;
        font-weight: 300;
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
</style>

<script>
import TransactionResource from '@/api/transaction';
import VendorResource from '@/api/vendor';

const transactionResource = new TransactionResource();
const vendorResource = new VendorResource();

export default {
  name: 'DetailTransaction',
  // components: { UserBio, UserCard, UserActivity },
  data() {
    return {
      user: {},
      transaction: {},
      loading: true,
    };
  },
  watch: {
    '$route': 'getTransaction',
  },
  created() {
    const id = this.$route.params && this.$route.params.id;
    this.getTransaction(id);
  },
  methods: {
    async getTransaction(id) {
      const { data } = await transactionResource.get(id);
      this.transaction = data;
      console.log(this.transaction);
      this.loading = false;
    },
  },
};
</script>
