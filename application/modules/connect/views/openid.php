<!DOCTYPE HTML>
<html>
<head>
<meta charset="UTF-8">
<title><?php echo lang('connect_openid_page_name'); ?></title>
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

<h1><?php echo sprintf(lang('connect_enter_your'), lang('connect_openid_url')); ?></h1>

<div class="form">

<img src="<?php echo IMAGES_PATH; ?>auth/top.png" width="676" height="7" />

    <?php echo form_open(uri_string(), array('class'=>'register_form', 'autocomplete'=>'off')); ?>
	
	<div class="top">

        <div class="col_left">

            <div>

                <?php echo form_label(lang('connect_openid_url'), 'connect_openid_url'); ?>				
				<?php echo form_input(array(
                        'name' => 'connect_openid_url',
                        'id' => 'connect_openid_url',
                        'value' => set_value('connect_openid_url'),
                        'maxlength' => '24',
						'class' => 'required',						
						'tabindex' => '1',
						'required' => 'required'
                    ));echo anchor($this->config->item('openid_what_is_url'), lang('connect_start_what_is_openid'), array('target' => '__blank'));?>
            </div>

        </div><!-- col_left -->
		
		<div class="col_right">
			
		</div>

        <div class="clear"></div>
		
		<?php if(isset($connect_openid_error)) : ?> 
			<div class="errors">			
				<p><?php echo $connect_openid_error; ?></p>		
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
                        'content' => '<span>'.lang('connect_proceed').'</span>',
						'tabindex' => '2'
                    )); 
				?>

            </div><!-- right -->

            <div class="clear"></div>

        </div><!-- button -->

    </div><!-- bottom -->

    <img src="<?php echo IMAGES_PATH; ?>auth/bottom.png" width="676" height="13" />

    <?php echo form_close(); ?>

</div><!-- form -->

</div><!-- main -->
</body>
</html>
