<!-- {#
 # SharIF Judge
 # file: base.twig
 # author: Mohammad Javad Naderi <mjnaderi@gmail.com>
 #} -->
<?= $this->renderSection('base')?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title><?= $this->renderSection('head_title') ?> - SharIF Judge</title>
	<meta content="text/html" charset="UTF-8">
	<link rel="icon" href=" <?= base_url('assets/images/favicon.ico') ?> "/>
	<link rel="stylesheet" type='text/css' href="<?= base_url('assets/styles/main.css') ?>"/>
	<script type="text/javascript" src="<?= base_url('assets/js/jquery-1.10.2.min.js') ?>"></script>
	<!-- You can use google's cdn for loading jquery: -->
	<!-- <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script> -->
	<script type="text/javascript" src="<?= base_url('assets/js/jquery-ui-1.10.3.custom.min.js') ?>"></script>
	<link rel="stylesheet" href="<?= base_url('assets/styles/flick/jquery-ui-1.10.3.custom.min.css') ?>"/>
	<script type="text/javascript" src="<?= base_url('assets/js/moment.min.js') ?>"></script>
	<script type="text/javascript" src="<?= base_url('assets/js/jquery.hoverIntent.minified.js') ?>"></script>
	<script type="text/javascript" src="<?= base_url('assets/js/jquery.cookie.js') ?>"></script>
	<script type="text/javascript" src="<?= base_url('assets/js/jquery.ba-resize.min.js') ?>"></script>

	<script type="text/javascript" src="<?= base_url('assets/noty/jquery.noty.js') ?>"></script>
	<script type="text/javascript" src="<?= base_url('assets/noty/layouts/center.js') ?>"></script>
	<script type="text/javascript" src="<?= base_url('assets/noty/layouts/bottomRight.js') ?>"></script>
	<script type="text/javascript" src="<?= base_url('assets/noty/themes/default.js') ?>"></script>

	<link rel='stylesheet' type='text/css' href='<?= base_url('assets/nano_scroller/nanoscroller.css') ?>'/>
	<script type='text/javascript' src="<?= base_url('assets/nano_scroller/jquery.nanoscroller.min.js') ?>"></script>

	<link rel='stylesheet' type='text/css' href='<?= base_url('assets/font/font-awesome/css/font-awesome.min.css') ?>'/>

</head>
<script>
shj={};
shj.site_url = '<?= rtrim(site_url(),'/') ?>/';
shj.base_url = '<?= rtrim(base_url(),'/') ?>/';
shj.csrf_token = $.cookie('shjcsrftoken');
shj.offset = moment('<?= shj_now_str() ?>').diff(moment());
shj.time = moment();
shj.finish_time = moment("<?= $finish_time ?>");
shj.extra_time = moment.duration("<?= $extra_time ?>", 'seconds');
// notifications
shj.check_for_notifications = false;
shj.notif_check_delay = 30;
shj.color_scheme = 'github';
</script>

<script type="text/javascript" src="<?= base_url('assets/js/shj_functions.js') ?>"></script>

<?= $this->renderSection('other_assets')?>

<body id="body">
<a href="#page_title" class="skip">Skip to content</a>

<?= $this->include('templates/top_bar') ?>
<?= $this->include('templates/side_bar') ?>
<div id="main_container" class="scroll-wrapper">
	<div class="scroll-content">

		<div id="page_title">
			<i class="fa <?= $this->renderSection('icon') ?>"></i>
			<h1 dir="auto"><?= $this->renderSection('title') ?></h1>
			<?= $this->renderSection('title_menu')?>
		</div>

		<div id="main_content">
			<?= $this->renderSection('main_content')?>
		</div>
	</div>
</div>
<?= $this->renderSection('body_end') ?>
</body>
</html>