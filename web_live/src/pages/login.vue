<template>
   <el-form :model="loginForm" :rules="rules" ref="loginForm" class="demo-ruleForm longin-box">
        <h3 class="title" v-text="'系统后台登录'"></h3>
        <el-form-item prop="username">
            <el-input type="text" v-model="loginForm.username" auto-complete="off"></el-input>
        </el-form-item>
        <el-form-item prop="pass">
            <el-input type="password" v-model="loginForm.pass" auto-complete="off" ></el-input>
        </el-form-item>
        <el-form-item>
            <el-input type="text" placeholder="请输入验证码" style="width:50%"></el-input><img src=""  title="点击刷新验证码" style="width:45%"/>
        </el-form-item>
        <el-form-item prop="checked">
            <el-checkbox v-model="loginForm.checked">记住密码</el-checkbox>
        </el-form-item>
        <el-button type="primary" >登  录</el-button>
    </el-form>
 
</template>
<script type="text/javascript">
  export default{
        data(){
            return {
                loginForm: {
                    username: "",
                    pass: "",
                    checked: true
                }
            }
        }
    }
</script>
<style type="text/css">
.longin-box{
		width: 300px;
		margin: 120px auto;
		background-color: #F9FAFC;
		padding: 20px;
		border-radius: 10px;
		-moz-border-radius: 5px;
		background-clip: padding-box;
		border: 2px solid #20A0FF;
	}
	.longin-box .el-form-item{
        width:80%;
        margin:0 auto;
        margin-top:20px;
	}
	.longin-box .el-form-item input{
	background:#D3DCE6;
	}
	.longin-box .el-form-item img{
	vertical-align:middle;
	border:none;
	}
	.longin-box button{
        width:80%;
        margin:0 auto;
        margin-left:10%;
	}
	.title{
		margin-bottom: 40px;
		color: #505458;
		text-align: center;
	}

</style>