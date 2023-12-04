<!-- {#
 # SharIF Judge
 # file: simple_header.twig
 # author: Mohammad Javad Naderi <mjnaderi@gmail.com>
 #} -->
<?= $this->renderSection('simple_header') ?>
<head>
	<title><?= $title ?> - SharIF Judge</title>
	<meta content="text/html" charset="UTF-8">
	<link rel="icon" href="<?= base_url("/assets/images/favicon.ico") ?>"/>
	<link rel="stylesheet" type='text/css' href="<?= base_url("/assets/styles/login.css") ?>"/>
</head>