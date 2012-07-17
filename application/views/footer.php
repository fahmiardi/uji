<div id="footer">
    <div><?php echo sprintf(lang('website_footer'), date('Y')." ".lang('website_title')); ?></div>
    <div><?php echo sprintf(lang('website_page_rendered_in_x_seconds'), $this->benchmark->elapsed_time()); ?></div>
</div>
