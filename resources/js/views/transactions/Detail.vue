<template>
  <div class="app-container">
    Transaction DETAIL
  </div>
</template>

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
