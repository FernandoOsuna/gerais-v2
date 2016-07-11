<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>Rapyd Demo</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<style type="text/css">

body {
 background-color: #fff;
 margin: 40px;
 font-family: Lucida Grande, Verdana, Sans-serif;
 font-size: 12px;
 color: #4F5155;
}
h1 { color: #444; background-color: transparent; border-bottom: 1px solid #D0D0D0; font-size: 16px; font-weight: bold; margin: 24px 0 2px 0; padding: 5px 0 6px 0; }
h2 { color: #333; background-color: #efefef; font-weight: normal; font-size: 22px; margin: 0 0 15px 0; padding: 3px 2px 3px 10px; }
h3 { margin: 0; padding: 10px 0 5px 0;}
a { color: #003399; background-color: transparent; font-weight: normal; }

#wrap { width: 770px; margin: 0 auto;}
#left {float:left; width: 190px;  padding: 5px 5px 0 5px; }
#right {float:left; width: 540px; padding: 5px 5px 0 15px;  color: #000;  border-left: 1px solid #e6e6e6;}

.line {height: 2px; margin: 10px 0 10px 0;}
.footer { clear: both; color: #999999; padding: 10px 0 10px 0; border-top: 1px solid #e6e6e6; text-align: center; line-height: 13px;}
.footer a { text-decoration: underline; }
.content {float:left; width:100%}
.code { clear:left; margin: 0 0 20px 0; font: 11px "courier new",Tahoma,Arial,sans-serif;	background: #eFeFeF;	padding: 10px;	border: 1px solid #dddddd; }
.note {padding: 10px; background-color: #FFFFCC; color:#000}
.note hr { border: none 0; border-bottom: 1px solid #D9D900; height:1px;}

#comments { width: 95%; }

</style>

<?php echo $head?>
</head>
<body>

<div id="wrap">

  <h1>Rapyd Demo</h1>
  <div>
    <div style="float:left; width:230px">
      Rapyd <?php echo RAPYD_VERSION?>
    </div>
  </div>

  <div class="line"></div>


  <div id="left">


    <div><a href="<?php echo rpd::url('demo')?>">Index</a></div>
    <div class="line"></div>

    <h3>Samples</h3>
    <div><img src="<?php echo rpd::asset('table.png')?>" style="vertical-align:middle" /> <a href="<?php echo rpd::url('grid/index')?>">DataGrid</a></div>
    <div><img src="<?php echo rpd::asset('magnifier.png')?>" style="vertical-align:middle" /> <a href="<?php echo rpd::url('filtered_grid/index')?>">DataGrid + DataFilter</a></div>
    <div class="line"></div>
    <div><img src="<?php echo rpd::asset('application_form.png')?>" style="vertical-align:middle" /> <a href="<?php echo rpd::url('form/index')?>">DataForm</a></div>
    <div class="line"></div>
    <div><img src="<?php echo rpd::asset('application_form_edit.png')?>" style="vertical-align:middle" /> <a href="<?php echo rpd::url('edit/index/show/1')?>">DataEdit</a></div>
    <div><img src="<?php echo rpd::asset('table_edit.png')?>" style="vertical-align:middle" /> <a href="<?php echo rpd::url('edit_grid/article/show/1')?>">DataEdit + DataGrid</a></div>
    <div><img src="<?php echo rpd::asset('page_save.png')?>" style="vertical-align:middle" /> <a href="<?php echo rpd::url('upload/show')?>">Array Driven DG + Upload</a></div>

    <div class="line"></div>

    <h3>Links</h3>
    <div><img src="<?php echo rpd::asset('help.png')?>" style="vertical-align:middle" /> <a href="http://code.google.com/p/rapyd-framework/w/list">Documentation</a></div>
    <div><img src="<?php echo rpd::asset('world.png')?>" style="vertical-align:middle" /> <a href="http://www.rapyd.com">Rapyd Website</a> </div>
    <div><img src="<?php echo rpd::asset('heart.png')?>" style="vertical-align:middle" /> <a href="http://www.rapyd.com/support">Donate</a> :(</div>

    <div class="line"></div>

  </div>

  <div id="right">

    <div class="content">

     <h2><?php echo $title?></h2>

     <?php echo $content?>

			<div class="line"></div>
    </div>

  </div>

  <div class="line"></div>

<?php if ($code!=''): ?>
  <div class="code">
CODE <br />
    <?php echo $code?>
  </div>
  <div class="line"></div>
<?php endif;?>

  <div class="footer">
    <p>
     <?php echo strftime("%A %d %B %Y", strtotime(RAPYD_BUILD_DATE))?> | ver <?php echo RAPYD_VERSION?> | rendered in {time} / {memory} </p>
  </div>

</div>


</body>
</html>
