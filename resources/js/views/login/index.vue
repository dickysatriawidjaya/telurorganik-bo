<template>
  <div class="login-container">
    <img class="logo-login" :src="'/images/logo.png'">
    <el-form ref="loginForm" :model="loginForm" :rules="loginRules" class="login-form" auto-complete="on" label-position="left">
      <h3 class="title">
        Login
      </h3>
      <el-row>
        <el-col :span="8">
          <h3 class="text_normal">
            username :
          </h3>
        </el-col>
        <el-col :span="16">
          <el-form-item prop="email">
            <el-input v-model="loginForm.email" name="email" type="text" auto-complete="on" :placeholder="$t('login.email')" />
          </el-form-item>
        </el-col>
      </el-row>
      <el-row>
        <el-col :span="8">
          <h3 class="text_normal">
            Password :
          </h3>
        </el-col>
        <el-col :span="16">
          <el-form-item prop="password">
            <el-input
              v-model="loginForm.password"
              :type="pwdType"
              name="password"
              auto-complete="on"
              placeholder="password"
              @keyup.enter.native="handleLogin"
            />
            <span class="show-pwd" @click="showPwd">
              <svg-icon icon-class="eye" />
            </span>
          </el-form-item>
        </el-col>
      </el-row>
      <div class="button_login">
        <el-button :loading="loading" type="telur" style="height:29px;width:104px;margin-top:20px;" @click.native.prevent="handleLogin">
          Sign in
        </el-button>
      </div>
    </el-form>
    <div class="foot_kerjain">
      Created by : kerjain.bandung-2019
    </div>
  </div>
</template>

<script>
import LangSelect from '@/components/LangSelect';
import { validEmail } from '@/utils/validate';
import Cookies from 'js-cookie';

export default {
  name: 'Login',
  components: { LangSelect },
  data() {
    const validateEmail = (rule, value, callback) => {
      if (!validEmail(value)) {
        callback(new Error('Please enter the correct email'));
      } else {
        callback();
      }
    };
    const validatePass = (rule, value, callback) => {
      if (value.length < 4) {
        callback(new Error('Password cannot be less than 4 digits'));
      } else {
        callback();
      }
    };
    return {
      loginForm: {
        email: 'admin@laravue.dev',
        password: 'laravue',
      },
      loginRules: {
        email: [{ required: true, trigger: 'blur', validator: validateEmail }],
        password: [{ required: true, trigger: 'blur', validator: validatePass }],
      },
      loading: false,
      pwdType: 'password',
      redirect: undefined,
    };
  },
  watch: {
    $route: {
      handler: function(route) {
        this.redirect = route.query && route.query.redirect;
      },
      immediate: true,
    },
  },
  methods: {
    showPwd() {
      if (this.pwdType === 'password') {
        this.pwdType = '';
      } else {
        this.pwdType = 'password';
      }
    },
    handleLogin() {
      this.$refs.loginForm.validate(valid => {
        if (valid) {
          this.loading = true;
          this.$store.dispatch('user/login', this.loginForm)
            .then(() => {
              window.location.reload(false);

              if (Cookies.get('Role') == 'admin'){
                console.log('masuk admin' + this.redirect);
                this.$router.push({ path: this.redirect || '/' });
              } else {
                console.log('masuk bukan admin' + this.redirect);
                this.$router.push({ path: this.redirect || '/transaction' });
              }

              this.loading = false;
            })
            .catch(() => {
              this.loading = false;
            });
        } else {
          console.log('error submit!!');
          return false;
        }
      });
    },
  },
};
</script>

<style rel="stylesheet/scss" lang="scss">
$bg:#2d3a4b;
$light_gray:#eee;
.el-button--telur {
    font-size:17px;
    line-height:19px;
    font-weight:500;
    color:#ffffff;
    background: rgb(2,0,36);
    background: linear-gradient(90deg, rgba(2,0,36,1) 0%, rgba(207,233,37,1) 0%, rgba(103,186,54,1) 100%);
    border: #FDBF49;
    padding: 0;
}
.foot_kerjain{
  position:absolute;
  left:43%;
  bottom: 1%;
  font-size: 10px;
  font-weight: 500;
  color: #FFFFFF;
  margin: 0px auto 20px auto;
  text-align: center;
  font-family: 'Ubuntu', sans-serif;
}
/* reset element-ui css */
.login-container {
  .el-input {
    display: inline-block;
    height: 29px;
    width: 85%;
    input {
      background: transparent;
      border: 0px;
      -webkit-appearance: none;
      border-radius: 0px;
      // padding: 7px 5px 7px 15px;
      color: #B2B2B2;
      height: 29px;
      &:-webkit-autofill {
        -webkit-box-shadow: 0 0 0px 1000px $bg inset !important;
        -webkit-text-fill-color: #fff !important;
      }
    }
  }
  .el-form-item {
    border: 1px solid rgba(255, 255, 255, 0.1);
    background: #ffffff;
    border-radius: 5px;
    color: #B2B2B2;
    line-height:20px;
    margin-top:10px;
    margin-bottom:3.6px;
  }
}

</style>

<style rel="stylesheet/scss" lang="scss" scoped>
$bg:#2d3a4b;
$dark_gray:#889aa4;
$light_gray:#eee;
.login-container {
  position: fixed;
  height: 100%;
  width: 100%;
  background-color: $bg;
  background: url(/images/bg.png) no-repeat center center fixed;
  background-size: cover;
  img.logo-login{
    position: absolute;
    left: 46%;
    right: 0;
    top: 115px;
    max-width: 100px;
  }
  .login-form {
    position: absolute;
    left: 0;
    right: 0;
    width: 575px;
    height:280px;
    border-radius: 10px;
    max-width: 100%;
    padding:25px 85px 35px 85px;
    margin: 310px auto;
    background:rgba(112, 112, 112, 0.56);
    .el-form-item__content{
      background:#FFFFFF;
      color:#B2B2B2;
      font-size:13px;
    }
  }

  .tips {
    font-size: 14px;
    color: #fff;
    margin-bottom: 10px;
    span {
      &:first-of-type {
        margin-right: 16px;
      }
    }
  }
  .button_login{
    text-align: right;
    margin-bottom:10px;
  }
  .svg-container {
    padding: 6px 5px 6px 15px;
    color: $dark_gray;
    vertical-align: middle;
    width: 30px;
    display: inline-block;
  }
  .title {
    font-size: 34px;
    font-weight: 400;
    color: #FFFFFF;
    margin: 0px auto 20px auto;
    text-align: center;
    font-family: 'Abel', sans-serif;
  }
  .text_normal{
    font-size: 17px;
    font-weight: 300;
    color: #FFFFFF;
    text-align: center;
    font-family: 'Ubuntu', sans-serif;
  }
  .show-pwd {
    position: absolute;
    right: 10px;
    top: 7px;
    font-size: 16px;
    color: #D3D3D3;
    cursor: pointer;
    user-select: none;
  }
  .set-language {
    color: #fff;
    position: absolute;
    top: 40px;
    right: 35px;
  }
}
</style>
