<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo lang('sign_out_page_name'); ?></title>
<base href="<?php echo base_url(); ?>" />
<link rel="shortcut icon" href="" />
<link type="text/css" rel="stylesheet" href="" />
<link type="text/css" rel="stylesheet" href="" />
</head>
<body>
<?php echo $this->load->view('header'); ?>
<div id="content">
    <div>
            <h2><?php echo lang('sign_out_successful'); ?></h2>
            <p><?php echo anchor('', lang('sign_out_go_to_home'), array('class'=>'button')); ?></p>
    </div>
</div>
<?php echo $this->load->view('footer'); ?>
</body>
</html>
