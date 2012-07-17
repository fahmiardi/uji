<!DOCTYPE HTML>
<html>
<head>
<meta charset="UTF-8">
<title><?php echo lang('sign_up_page_name'); ?></title>
<link rel="stylesheet" type="text/css" href="<?php echo CSS_PATH; ?>auth.css" />
<link rel="stylesheet" type="text/css" href="<?php echo JS_PATH; ?>jqueryui/jquery-ui-1.8.7.custom.css" media="all" />


<script src="<?php echo JS_PATH; ?>jquery_new.js"></script>
<script src="<?php echo JS_PATH; ?>jqueryui/jquery-ui-1.8.12.custom.min.js"></script>
<script src="<?php echo JS_PATH; ?>jqpass.js"></script>
<script src="<?php echo JS_PATH; ?>jqvalidation.js"></script>


</head>

<body>
<div style="background:url() no-repeat; width:218px; height:44px;" class="logo"></div>
<div style="text-align:center;"><img src="<?php echo IMAGES_PATH; ?>auth/top_light.png" width="877" height="139" /></div>
<div class="main">
<script type="text/javascript">
    $.checkUsername = function() {
		
		
		
        var username = $('#sign_up_username').val();         



        $.ajax({

            type: "POST",

            url: "sign_up/check_username",            

            data: {uname: username},

            success: function(result) {

                if(result) {
					$("#sign_up_username").addClass("taken").removeClass("free");
				}
				else {
					 $("#sign_up_username").removeClass("taken").addClass("free");
				}

            }

        });

    }

	$(function(){

		$("#sign_up_username").blur(function(){

			$.checkUsername();

		});

	});

</script>

<style>

input#sign_up_username.taken {background:url(<?php echo IMAGES_PATH; ?>auth/taken.png) no-repeat right -126px;}

input#sign_up_username.free { background:url(<?php echo IMAGES_PATH; ?>auth/taken.png) no-repeat right 8px;}

</style>

<h1><?php echo lang('sign_up_page_name'); ?>
	<span class="right" style="font-size:12px; font-family:Verdana, Geneva, sans-serif; color:#666; margin-bottom:-5px;">
		<?php if ($this->config->item('social_media_enabled')) : ?>
			<?php $this->load->language('connect/connect'); ?>
			<span style='font-size:small;'><?php echo lang('connect_social_media'); ?></span>
			<?php foreach($this->config->item('social_media_providers') as $provider=>$property) : ?>				
				<?php if ($property['enabled']) : ?>
					<?php echo anchor('connect/'.$provider.'/?client=sign_up', "<img src=".$property['icon']." alt='' />", 
						array('title'=>sprintf(lang('connect_with_x'), lang('connect_'.$provider))) ); ?>
				<?php else : ?>
					<?php echo anchor('#disable', "<img src=".$property['icon']." alt='' />", 
						array('title'=>sprintf(lang('connect_with_x'), lang('connect_'.$provider))) ); ?>
				<?php endif; ?>
			<?php endforeach; ?>
		<?php endif; ?>
	</span>
</h1>

<div class="form">

<img src="<?php echo IMAGES_PATH; ?>auth/top.png" width="676" height="7" />

    <?php echo form_open(uri_string(), array('class'=>'register_form', 'autocomplete'=>'off')); ?>
	
	<div class="top">

        <div class="col_left">

            <div>

                <?php echo form_label(lang('sign_up_username'), 'sign_up_username'); ?>				
				<?php echo form_input(array(
                        'name' => 'sign_up_username',
                        'id' => 'sign_up_username',
                        'value' => set_value('sign_up_username'),
                        'maxlength' => '24',
						'class' => 'required',						
						'tabindex' => '1',
						'required' => 'required'
                    )); 
				?>

                <span><a href="javascript:void(0);" onclick="$.checkUsername();">check username</a></span>

            </div>

            <div>

                <?php echo form_label(lang('sign_up_first_name'), 'sign_up_first_name', array('class'=>'first_name')); ?>
				<?php echo form_label(lang('sign_up_last_name'), 'sign_up_last_name', array('class'=>'last_name')); ?>
				<?php 
					if(isset($account_linking[1]['firstname'])) {
						$isi = $account_linking[1]['firstname'];
					}
					else {
						if(isset($account_linking[1]['fullname'])) {
							$isi = $account_linking[1]['fullname'];
						}
						else {
							$isi = '';
						}
					}
				?>
				<?php echo form_input(array(
							'name' => 'sign_up_first_name',
							'id' => 'sign_up_first_name',
							'value' => set_value('sign_up_first_name') ? set_value('sign_up_first_name') : $isi,
							'class' =>'first',
							'tabindex' => '3',
							'required' => 'required'
						)); 
				?>
				<?php echo form_input(array(
							'name' => 'sign_up_last_name',
							'id' => 'sign_up_last_name',
							'value' => set_value('sign_up_last_name') ? set_value('sign_up_last_name') : isset($account_linking[1]['lastname']) ? $account_linking[1]['lastname'] : '',
							'class' =>'last',
							'tabindex' => '4',
							'required' => 'required'
						)); 
				?>

                <span></span>

            </div>

        </div><!-- col_left -->

        <div class="col_right">

            <div>

                <?php echo form_label(lang('sign_up_password'), 'sign_up_password'); ?>
				<?php echo form_password(array(
                        'name' => 'sign_up_password',
                        'id' => 'sign_up_password',
                        'value' => set_value('sign_up_password'),
						'class' => 'sign_up_password',
						'tabindex' => '2'
                    )); ?>

                <span><a href="#" id="show_password">show password</a></span>

            </div>

            <div>

                <?php echo form_label(lang('sign_up_email'), 'sign_up_email'); ?>
				<?php echo form_input(array(
                        'name' => 'sign_up_email',
                        'id' => 'sign_up_email',
                        'value' => set_value('sign_up_email'),
                        'maxlength' => '160',
						'class' => 'text email',
						'required' => 'required',
						'tabindex' > '5'
                    )); ?>

                <span></span>

            </div>

        </div><!-- col_right -->

        <div class="clear"></div>
		
		<?php echo form_error('sign_up_username'); ?>
		<?php echo form_error('sign_up_email'); ?>
		<?php if(isset($sign_up_username_error) || isset($sign_up_email_error)) : ?> 
			<div class="errors">
			<?php if(isset($sign_up_username_error)) : ?>
				<p><?php echo $sign_up_username_error; ?></p>		
			<?php elseif(isset($sign_up_email_error)) : ?>
				<p><?php echo $sign_up_email_error; ?></p>
			<?php endif; ?>
			<p>Unable to create account</p>
			</div>
		<?php endif; ?>
    
	</div><!-- top -->

    <div class="bottom">

        <div class="button">

            <div class="left" style="padding-top:8px;">

                <!--<label><input type="checkbox"  tabindex="6" name="agree_terms" required="required" />  <span style="padding-top:6px; display:inline-block;"> I agree to the <a href="#" class="terms">terms and conditions & privacy policy</a> of Freedcamp</span></label>-->

            </div><!-- left -->

            <div class="right">
				<?php echo form_button(array(
                        'type' => 'submit',
                        'class' => 'submitBtn',
                        'content' => '<span>'.lang('sign_up_create_my_account').'</span>',
						'tabindex' => '6'
                    )); ?>
				<?php echo lang('sign_up_already_have_account'); ?> <?php echo anchor('auth/sign_in', lang('sign_up_sign_in_now')); ?>

            </div><!-- right -->

            <div class="clear"></div>

        </div><!-- button -->

    </div><!-- bottom -->

    <img src="<?php echo IMAGES_PATH; ?>auth/bottom.png" width="676" height="13" />

    <?php echo form_close(); ?>

</div><!-- form -->

<script>

$(document).ready(function(e) {

	

	$(".terms").click(function(e){ e.preventDefault();

		$(".terms_and_conditions").fadeIn();

	});

	$(".terms_and_conditions .close").click(function(e){ e.preventDefault();

		$(".terms_and_conditions").fadeOut();

	});

	$(document).mouseup(function (e) { 

		if ($(e.target).parents(".terms_and_conditions").length == 0) {

			$(".terms_and_conditions").fadeOut();

		}

	});

	

    $('input[type=password]').password123({delay: 1500});

	$('#show_password').click( function() {

		alert('The password you entered is: '+$('.sign_up_password').val());

		return false;

	});

	$(".register_form").validate({

		rules: {

			first: "required",

			last: "required",

			sign_up_username: {

				required: true,

				minlength: 2

			},

			sign_up_password: {

				required: true,

				minlength: 6

			},

			email: {

				required: true,

				email: true

			},

			agree_terms: "required"

		},

		messages: {

			first: "Please enter your firstname",

			last: "Please enter your lastname",

			sign_up_username: {

				required: "Please enter a username",

				minlength: "Your username must consist of at least 2 characters"

			},

			sign_up_password: {

				required: "Please provide a password",

				minlength: "Your password must be at least 8 characters long"

			},

			email: "Please enter a valid email address",

			agree_terms: "Please accept our policy"

		}

	});

});

</script>

</div><!-- main -->
</body>
</html>
