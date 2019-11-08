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
                    123456 
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
                  <h3 class="text_detail">
                    paid
                  </h3>
                  <!-- <span v-if="scope.row.status == 1" style="color:#46A2FD">
                  Paid
                  </span>
                  <span v-if="scope.row.status == -1" style="color:#FD4646 !important">
                    Unpaid
                  </span> -->
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
                      Bos 
                    </h3>
                  </el-col>
              </el-row>
            </el-col>
            <el-col :span="12">
            </el-col>
        </el-row>
        <el-table v-loading="loading" :data="list" border fit highlight-current-row style="width: 100%">
        <el-table-column align="left" label="No." prop="index" width="60">
          <template slot-scope="scope">
            <span>{{ scope.row.index }}</span>
          </template>
        </el-table-column>
        <el-table-column align="left" class-name="status-col" label="Item" prop="created_at" width="120" sortable>
          <template slot-scope="scope">
            <span>{{ scope.row.created_at | moment("DD-MM-YYYY") }}</span>
          </template>
        </el-table-column>
        <el-table-column align="left" label="Price" prop="vendor" sortable>
          <template slot-scope="scope">
            <span v-if="scope.row.vendor.pic_name != '' || scope.row.vendor.pic_name">{{ scope.row.vendor.name }} ( {{ scope.row.vendor.pic_name }} )</span>
            <span v-else>{{ scope.row.vendor.name }}</span>
          </template>
        </el-table-column>
        <el-table-column align="left" label="Qty" prop="transaction_no" sortable width="140">
          <template slot-scope="scope">
            <span>{{ scope.row.transaction_no }}</span>
          </template>
        </el-table-column>
        <el-table-column align="left" label="Total" prop="total" sortable>
          <template slot-scope="scope">
            <span>{{ scope.row.total | toCurrency }}</span>
          </template>
        </el-table-column>
      </el-table>
       <el-row>
          <el-col :span="12" style="margin-left: 82%">
            <el-row>
                <el-col :span="5">
                  <h3 class="text_normal">
                    Total Price  
                  </h3>
                </el-col>
                <el-col :span="8">
                  <h3 class="text_normal">
                    1 juta 
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
      width:100%;
      border-radius: 10px;
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
      this.transction = data;
    },
  },
};
</script>
