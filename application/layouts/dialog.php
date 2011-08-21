<?php header ("Content-Type: text/html; charset=utf8", true); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
  <head>
    <title><?php echo get_page_title() ?></title>
<?php echo add_favicon_to_page('favicon.ico') ?>
<?php echo stylesheet_tag('dialog.css') ?> 
<?php echo stylesheet_tag('login.css') ?> 
<?php echo meta_tag('content-type', 'text/html; charset=utf-8', true) ?> 
<?php echo render_page_head() ?>
  </head>
  <body class="loginBody" style="text-align:center;">
  <div class="loginDiv">
    <table style="border-collapse:collapse;margin:150px auto 0;width:440px" border=0>
    	<tr>
    		<td class="t1">&nbsp;</td>
    		<td class="t2">&nbsp;</td>
    		<td class="t3">&nbsp;<div class="title"><?php echo get_page_title() ?></div></td>
    		<td class="t4">&nbsp;</td>
    		<td class="t5">&nbsp;</td>
    	</tr>
    	<tr height="19px">
    		<td class="mt1"></td>
    		<td rowspan=2 colspan=3 class="loginContents" >
			<div style="padding:10px 0px 10px 6px;">
<?php if(!is_null(flash_get('success'))) { ?>
          <div id="success" onclick="this.style.display = 'none'"><?php echo clean(flash_get('success')) ?></div>
<?php } ?>
<?php if(!is_null(flash_get('error'))) { ?>
          <div id="error" onclick="this.style.display = 'none'"><?php echo clean(flash_get('error')) ?></div>
<?php } ?>
<?php echo $content_for_layout ?>
    		</div>

			</td>
    		<td rowspan=2 class="m5">&nbsp;</td>
    	</tr>
    	<tr style="">
    		<td class="m1">&nbsp;</td>
    	</tr>
    	<tr>
    		<td class="b1">&nbsp;</td>
    		<td class="b2">&nbsp;</td>
    		<td class="b3">&nbsp;</td>
    		<td class="b4">&nbsp;</td>
    		<td class="b5">&nbsp;</td>
    	</tr>
    </table>
    </div>
  </body>
</html>