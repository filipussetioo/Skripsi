<!-- {#
 # SharIF Judge
 # file: submit.twig
 # author: Mohammad Javad Naderi <mjnaderi@gmail.com>
 #} -->
<?= $this->extend('templates/base') ?>
<?= $this->section('icon') ?>fa-location-arrow<?= $this->endSection()?>
<?= $this->section('title') ?>Submit<?= $this->endSection() ?>
<?= $this->section('head_title') ?>Submit<?= $this->endSection() ?>



<?= $this->section('other_assets') ?>
<link rel="stylesheet" type='text/css' href="<?= base_url('../assets/styles/submit.css') ?>"/>
	<script>
		shj.p={};
		<?= $problems_js ?>;
		$.ajax({
            url: "<?= site_url('assignments/pdfCheck/'.$user->selected_assignment['id']) ?>", 
            cache: false,
            success: function(data){
				data = JSON.parse(data);
				if(data.status){
					$("#pdf_viewer").attr('src', "<?= base_url('../assets/pdfjs/web/viewer.html?file=').site_url('assignments/pdf/' .$user->selected_assignment['id'] . '/null/true') ?>");
					$("#pdf_viewer").show();
				}
            },
            error: function (error){
                console.error(error);
            },
        });
	</script>
	<script src=<?= base_url('assets/ace/ace.js') ?>></script>
	<script type='text/javascript' src="<?= base_url("assets/js/shj_submit.js") ?>"></script>
<?= $this->endSection() ?>



<?= $this->section('main_content') ?>
<?php if ($error != 'none'): ?>
<p><?= $error ?></p>
<?php else: ?>
	<p>Selected assignment: <span dir="auto"><?= $user->selected_assignment['name'] ?></span></p>
	<p>Coefficient: <?= $coefficient ?>%</p>
	<?= form_open_multipart('submit') ?>
	<p class="input_p">
		<label for="problems" class="tiny">Problem:</label>
		<select id="problems" name ="problem" class="sharif_input">
			<option value="0" selected="selected">-- Select Problem --</option>
			<?php foreach($problems as $problem): ?>
				<option dir="auto" value="<?= $problem['id'] ?>"><?= $problem['name'] ?></option>
			<?php endforeach ?>
		</select>
		<div class="shj_error"><?= $validationError->hasError('problem') ? $validationError->getError('problem') : '' ?></div>
	</p>
	<p class="input_p">
		<label for="languages" class="tiny">Language:</label>
		<select id="languages" name="language" class="sharif_input">
			<option value="0" selected="selected">-- Select Language --</option>
		</select>
		<!-- form_error('language','<div class="shj_error">','</div>') ?> -->
	</p>
	<details>
		<summary>Submit by File Upload</summary>
		<p class="input_p upload_hidden">
			<label for="file" class="tiny">File:</label>
			<input type="file" id="file" class="sharif_input medium" name="userfile" />
			<?php if($upload_state == 'error'): ?>
			<div class="shj_error">Error uploading file.</div>
			<?php elseif ($upload_state == 'ok'): ?>
			<div class="shj_ok">File uploaded successfully. See the result in 'All Submissions'.</div>
			<?php endif ?>
		</p>
		<p class="input_p upload_hidden">
			<input type="submit" value="Submit" class="sharif_input"/>
		</p>
	</details>
	</form>

	<br>
	<details>
		<summary>Code, Test, and Submit using built-in editor</summary>
		<iframe id="pdf_viewer" src="" style="display: none"></iframe>
		<div id="ide_wrap">
			<fieldset id="editor_wrap">
				<legend>Code</legend>
				<div id="code_editor" ></div>
			</fieldset>
			<fieldset id="in_wrap">
				<legend>Input</legend>
				<textarea id="editor_input" class="in_out"></textarea>
			</fieldset>
			<fieldset id="out_wrap">
				<legend>Output</legend>
				<textarea id="editor_output" class="in_out" readonly></textarea>
			</fieldset>
		</div>
		<br>
		<button type="button" class="sharif_input" id="editor_save" disabled>Save</button>
		<button type="button" class="sharif_input" id="editor_execute" disabled>Save &amp; Execute</button>
		<button type="button" class="sharif_input" id="editor_submit" disabled>Save &amp; Submit</button>
		<span id="ajax_status"></span>
	</details>
<?php endif ?>
<?= $this->endSection() ?>