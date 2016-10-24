$(function () {

	var verifyUrl=$('#verify-img').attr('src');
		$('#verify-img').click(function(){
			$(this).attr('src',verifyUrl+'?'+Math.random());
		});
		//添加自定义的验证方法
		jQuery.validator.addMethod("user", function(value, element) {   
	    var tel = /^[a-zA-Z][\w]{4,16}$/;
	    return this.optional(element) || (tel.test(value));
	}, "以字母开头，5-17 字母、数字、下划线'_'");

		$('form[name=register]').validate({
			errorElement:'span',
			success : function (label) {
			label.addClass('success');
			},

			rules:{
				//验证的字段名
				account:{
						 	//validate内置的各个规则属性
						 	required:true,
						 	user:true,
						 	//异步验证规则
						 	remote:
									{
										url:checkAccount,
										type:'post',
										dataType:'json',
										data:
											{
												account:function(){
													return $('#account').val();
												}

											}
									}
					

						},

				pwd:    {
							required:true,
							user:true,
						},

				pwded: {
						 	required:true,
						 	equalTo:"#pwd"

						},

				uname:  {
							required:true,
							rangelength:[3,15],
							remote:
									{
										url:checkUname,
										type:'post',
										dataType:'json',
										data:
											{
												uname:function(){
													return $('#uname').val();
												}

											}
									}
						},

				verify: {
							required:true,
							remote:
									{
										url:checkVerify,
										type:'post',
										dataType:'json',
										data:
											{
												verify:function(){
													return $('#verify').val();
												}

											}
									}
						}
				
					},

			messages:{
				account:{	
							required:'账户不能为空',
							remote:'账户已经存在'

						},

				pwd:    {
							required:'密码不能为空'
					    },

				pwded:  {
							required:'密码不能为空',
							equalTo:'两次密码不一致',
						},
				uname:  {
							required:'请输入你的昵称',
							rangelength:'昵称长度应在3-10字符区间',
							remote:'昵称已经存在'
						},
				verify: {
							required: '',
							remote : ' '
						}

				



						}


		})







	});