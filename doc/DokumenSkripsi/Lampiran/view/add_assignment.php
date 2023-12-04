<!-- {#
 # SharIF Judge
 # file: add_assignment.twig
 # author: Mohammad Javad Naderi <mjnaderi@gmail.com>
 #} -->
<?= $this->extend('templates/base') ?>
<?= $this->section('icon') ?><?= $edit ? 'fa-edit' : 'fa-plus-square' ?><?= $this->endSection()?>
<?= $this->section('title') ?><?= $edit ? 'Edit' : 'Add' ?><?= $this->endSection() ?>
<?= $this->section('head_title') ?><?= $edit ? 'Edit' : 'Add' ?><?= $this->endSection() ?>


<?= $this->section('other_assets') ?>
<script type='text/javascript' src="<?= base_url('assets/js/taboverride.min.js') ?>"></script>
<script>
	$(document).ready(function(){
		tabOverride.set(document.getElementsByTagName('textarea'));
		$switch = false;
		$("textarea").keyup(function (e) {
			if (e.which==27){
				tabOverride.set(document.getElementsByTagName('textarea'),$switch);
				$switch = !$switch;
    		}
		});
	});
</script>
<script type="text/javascript" src="<?= base_url('assets/js/jquery-ui-timepicker-addon.js') ?>"></script>
<script>
	shj.num_of_problems=<?= count($problems) ?>;
	shj.row='<tr><td>PID</td>\
	<td><input aria-label="Problem Name" type="text" name="name[]" class="sharif_input short" value="Problem "/></td>\
	<td><input aria-label="Score" type="text" name="score[]" class="sharif_input tiny2" value="100"/></td>\
	<td><input aria-label="Time Limit for C" type="text" name="c_time_limit[]" class="sharif_input tiny2" value="500"/></td>\
	<td><input aria-label="Time Limit for Python" type="text" name="python_time_limit[]" class="sharif_input tiny2" value="1500"/></td>\
	<td><input aria-label="Time Limit for Java" type="text" name="java_time_limit[]" class="sharif_input tiny2" value="2000"/></td>\
	<td><input aria-label="Memory Limit" type="text" name="memory_limit[]" class="sharif_input tiny" value="50000"/></td>\
	<td><input aria-label="Allowed Languages" type="text" name="languages[]" class="sharif_input short2" value="C,C++,Python 2,Python 3,Java"/></td>\
	<td><input aria-label="Diff Command" type="text" name="diff_cmd[]" class="sharif_input tiny" value="diff"/></td>\
	<td><input aria-label="Diff Argument" type="text" name="diff_arg[]" class="sharif_input tiny" value="-bB"/></td>\
	<td><input aria-label="Upload Only" type="checkbox" name="is_upload_only[]" class="check" value="PID"/></td>\
	<td><button class="delete_problem" type="button" aria-label="Delete Problem"><i class="fa fa-times-circle fa-lg color1 pointer"></i></button></td>\
</tr>';
	$(document).ready(function(){
		$("#add").click(function(){
			$('#problems_table>tbody').append(shj.row.replace(/PID/g, (shj.num_of_problems+1)));
			shj.num_of_problems++;
			$('#nop').attr('value', shj.num_of_problems);
		});
    $("#form_a_archived_assignment").click(function(){
			if ($("#form_a_archived_assignment").is(':checked')) {
        $("#start_time").val('1970-01-02 00:00:00');
        $("#finish_time").val('2038-01-18 00:00:00');
        $("#form_extra_time").val('0');
      }
      else{
        $("#start_time").val('');
        $("#finish_time").val('');
        $("#form_extra_time").val('');
      }
		});
		$(document).on('click', '.delete_problem', function(){
			if (shj.num_of_problems==1) return;
			var row = $(this).parents('tr');
			row.remove();
			var i = 0;
			$('#problems_table>tbody').children('tr').each(function(){
				i++;
				$(this).children(':first').html(i);
				$(this).find('[type="checkbox"]').attr('value',i);
			});
			shj.num_of_problems--;
			$('#nop').attr('value',shj.num_of_problems);
		});
		$('#start_time').datetimepicker({
			timeFormat: 'HH:mm:ss'
		});
		$('#finish_time').datetimepicker({
			timeFormat: 'HH:mm:ss'
		});
	});
</script>
<?= $this->endSection() ?>



<?= $this->section('title_menu') ?>
<span class="title_menu_item">
	<a href="https://github.com/ifunpar/Sharif-Judge/blob/docs/v1.4/add_assignment.md" target="_blank"><i class="fa fa-question-circle color1"></i> Help</a>
</span>
<?= $this->endSection() ?>



<?= $this->section('main_content') ?>
<?php $msgclasses = ['success' => 'shj_g', 'notice' => 'shj_o', 'error' => 'shj_r'] ?>
<?php foreach($messages as $message): ?>
	<p class="<?= $msgclasses[$message->type] ?>"><? $message->text ?></p>
<?php endforeach ?>

<?php if($edit): ?>
<p>
	<i class="fa fa-info-circle fa-lg color8"></i> If you don't want to change tests or pdf file, just do not upload its file.
</p>
<?php endif ?>

<?= form_open_multipart($edit ? 'assignments/edit/'.$edit_assignment['id'] : 'assignments/add') ?>
<div class="panel_left">
	<input type="hidden" name="number_of_problems" id="nop" value="<?= $edit ? $edit_assignment['problems'] : count($problems) ?>"/>
	<p class="input_p">
		<label for="form_a_name">Assignment Name</label>
		<input id="form_a_name" type="text" name="assignment_name" class="sharif_input medium" value="<?= $edit ? $edit_assignment['name'] : set_value('assignment_name') ?>"/>
		<div class="shj_error"><?= $validationError->hasError('assignment_name') ? $validationError->getError('assignment_name') : '' ?></div>
	</p>
	<p class="input_p">
		<label for="start_time">Start Time</label>
		<input id="start_time" type="text" name="start_time" class="sharif_input medium" value="<?= $edit ? date('m/d/Y H:i:s', strtotime($edit_assignment['start_time'])) : set_value('start_time') ?>" />
		<div class="shj_error"><?= $validationError->hasError('start_time') ? $validationError->getError('start_time') : '' ?></div>
	</p>
	<p class="input_p">
		<label for="finish_time">Finish Time</label>
		<input id="finish_time" type="text" name="finish_time" class="sharif_input medium" value="<?= $edit ? date('m/d/Y H:i:s', strtotime($edit_assignment['finish_time'])) : set_value('finish_time') ?>" />
		<div class="shj_error"><?= $validationError->hasError('finish_time') ? $validationError->getError('finish_time') : '' ?></div>
	</p>
	<p class="input_p clear">
		<label for="form_extra_time">
			Extra Time (minutes)<br>
			<span class="form_comment">Extra time for late submissions.</span>
		</label>
		<input id="form_extra_time" type="text" name="extra_time" class="sharif_input medium" value="<?= $edit ? $twig->extra_time_formatter($edit_assignment['extra_time']) : set_value('extra_time') ?>" />
		<div class="shj_error"><?= $validationError->hasError('extra_time') ? $validationError->getError('extra_time') : '' ?></div>
	</p>
	<p class="input_p clear">
		<label for="form_participants">Participants<br>
			<span class="form_comment">Enter username of participants here (comma separated).
				Only these users are able to submit. You can use keyword "ALL".</span>
			<span class="form_comment clear">Press "esc" to enable/disable tabindent</span>
		</label>
		<textarea id="form_participants" name="participants" rows="5" class="sharif_input medium"><?= $edit ? $edit_assignment['participants'] : set_value('participants', 'ALL') ?></textarea>
	</p>
	<p class="input_p clear">
		<label for="form_tests_desc">Tests and Descriptions (zip file)<br>
			<span class="form_comment">
				<a href="https://github.com/ifunpar/Sharif-Judge/blob/docs/v1.4/tests_structure.md" target="_blank">Use this structure</a>
			</span>
		</label>
		<input id="form_tests_desc" type="file" name="tests_desc" class="sharif_input medium"/>
	</p>
	<p class="input_p clear">
		<label for="form_pdf">PDF File<br>
			<span class="form_comment">
				PDF File of Assignment
			</span>
		</label>
		<input id="form_pdf" type="file" name="pdf" class="sharif_input medium"/>
	</p>
</div>
<div class="panel_right">
	<p class="input_p">
		<input id="form_a_open" type="checkbox" name="open" value="1" <?= $edit ? ($edit_assignment['open'] ? 'checked' : '') : set_checkbox('open', '1') ?> />
		<label for="form_a_open" class="default">Open</label>
		<span class="form_comment space-left">Open or close this assignment</span>
		<div class="shj_error"><?= $validationError->hasError('open') ? $validationError->getError('open') : '' ?></div>
	</p>
	<p class="input_p">
		<input id="form_a_scoreboard" type="checkbox" name="scoreboard" value="1" <?= $edit ? ($edit_assignment['scoreboard'] ? 'checked' : '') : set_checkbox('scoreboard', '1') ?> />
		<label for="form_a_scoreboard" class="default">Scoreboard</label>
		<span class="form_comment space-left">Check this to enable scoreboard</span>
		<div class="shj_error"><?= $validationError->hasError('scoreboard') ? $validationError->getError('scoreboard') : '' ?></div>
	</p>
	<p class="input_p">
		<input id="form_a_javaexceptions" type="checkbox" name="javaexceptions" value="1" <?= $edit ? ($edit_assignment['javaexceptions'] ? 'checked' : '') : set_checkbox('javaexceptions', '1') ?> />
		<label for="form_a_javaexceptions" class="default">Java Exceptions</label>
		<span class="form_comment space-left">Check this to show Java exceptions to users</span>
		<div class="shj_error"><?= $validationError->hasError('javaexceptions') ? $validationError->getError('javaexceptions') : '' ?></div>
	</p>
  <p class="input_p">
		<input id="form_a_archived_assignment" type="checkbox" name="archived_assignment" value="1" <?= $edit ? ($edit_assignment['archived_assignment'] ? 'checked' : '') : set_checkbox('archived_assignment', '1') ?> />
		<label for="form_a_archived_assignment" class="default">Archived Assignment</label>
		<span class="form_comment space-left">Check this to make an archived assignment</span>
		<div class="shj_error"><?= $validationError->hasError('archived_assignment') ? $validationError->getError('archived_assignment') : '' ?></div>
	</p>
	<p class="input_p">
		<label for="form_late_rule">Coefficient rule (<a target="_blank" href="https://github.com/ifunpar/Sharif-Judge/blob/docs/v1.4/add_assignment.md#coefficient-rule">?</a>)</label><br>
		<span class="form_comment medium clear" style="display: block;">PHP script without &lt;?php ?&gt; tags</span>
		<span class="form_comment clear">Press "esc" to enable/disable tabindent</span><br>
		<textarea id="form_late_rule" name="late_rule" rows="20" class="sharif_input add_text"><?= $edit ? $edit_assignment['late_rule'] : set_value('late_rule', $default_late_rule) ?></textarea>
		<div class="shj_error"><?= $validationError->hasError('late_rule') ? $validationError->getError('late_rule') : '' ?></div>
	</p>
</div>
<p class="input_p" id="add_problems">Problems <button type="button" id="add" aria-label="Add Problem"><i class="fa fa-plus-circle fa-lg color11 pointer"></i></button>
<table id="problems_table">
	<thead>
	<tr>
		<th rowspan="2"></th>
		<th rowspan="2">Name</th>
		<th rowspan="2">Score</th>
		<th colspan="3" style="border-bottom: 1px solid #BDBDBD">Time Limit (ms)</th>
		<th rowspan="2">Memory<br>Limit (kB)</th>
		<th rowspan="2">Allowed<br>Languages (<a aria-label="Link Help For Languages" target="_blank" href="https://github.com/ifunpar/Sharif-Judge/blob/docs/v1.4/add_assignment.md#allowed-languages">?</a>)</th>
		<th rowspan="2">Diff<br>Command (<a aria-label="Link Help For Diff Command" target="_blank" href="https://github.com/ifunpar/Sharif-Judge/blob/docs/v1.4/add_assignment.md#diff-command">?</a>)</th>
		<th rowspan="2">Diff<br>Argument (<a aria-label="Link Help For Diff Argument" target="_blank" href="https://github.com/ifunpar/Sharif-Judge/blob/docs/v1.4/add_assignment.md#diff-arguments">?</a>)</th>
		<th rowspan="2">Upload<br>Only (<a aria-label="Link Help For Upload Only" target="_blank" href="https://github.com/ifunpar/Sharif-Judge/blob/docs/v1.4/add_assignment.md#upload-only">?</a>)</th>
		<th rowspan="2"></th>
	</tr>
	<tr>
		<th>C/C++</th><th>Python</th><th>Java</th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($problems as $problem): ?>
		<tr>
			<td><?= $problem['id'] ?></td>
			<td><input aria-label="Problem Name" type="text" name="name[]" class="sharif_input short" value="<?= $problem['name'] ?>"/></td>
			<td><input aria-label="Score" type="text" name="score[]" class="sharif_input tiny2" value="<?= $problem['score'] ?>"/></td>
			<td><input aria-label="Time Limit for C" type="text" name="c_time_limit[]" class="sharif_input tiny2" value="<?= $problem['c_time_limit'] ?>"/></td>
			<td><input aria-label="Time Limit for Python" type="text" name="python_time_limit[]" class="sharif_input tiny2" value="<?= $problem['python_time_limit'] ?>"/></td>
			<td><input aria-label="Time Limit for Java" type="text" name="java_time_limit[]" class="sharif_input tiny2" value="<?= $problem['java_time_limit'] ?>"/></td>
			<td><input aria-label="Memory Limit" type="text" name="memory_limit[]" class="sharif_input tiny" value="<?= $problem['memory_limit'] ?>"/></td>
			<td><input aria-label="Allowed Languages" type="text" name="languages[]" class="sharif_input short2" value="<?= $problem['allowed_languages'] ?>"/></td>
			<td><input aria-label="Diff Command" type="text" name="diff_cmd[]" class="sharif_input tiny" value="<?= $problem['diff_cmd'] ?>"/></td>
			<td><input aria-label="Diff Argument" type="text" name="diff_arg[]" class="sharif_input tiny" value="<?= $problem['diff_arg'] ?>"/></td>
			<td><input aria-label="Upload Only" type="checkbox" name="is_upload_only[]" class="check" value="<?= $problem['id'] ?>" <?= $problem['is_upload_only'] ? 'checked' : '' ?>/></td>
			<td><button class="delete_problem" type="button" aria-label="Delete Problem"><i class="fa fa-times-circle fa-lg color1 pointer"></i></td>
		</tr>
	<?php endforeach ?>
	</tbody>
</table>
</p>
<div class="shj_error"><?= $validationError->hasError('name.*') ? $validationError->getError('name.*') : '' ?></div>
<div class="shj_error"><?= $validationError->hasError('score.*') ? $validationError->getError('score.*') : '' ?></div>
<div class="shj_error"><?= $validationError->hasError('c_time_limit/*') ? $validationError->getError('c_time_limit.*') : '' ?></div>
<div class="shj_error"><?= $validationError->hasError('python_time_limit.*') ? $validationError->getError('python_time_limit.*') : '' ?></div>
<div class="shj_error"><?= $validationError->hasError('java_time_limit.*') ? $validationError->getError('java_time_limit.*') : '' ?></div>
<div class="shj_error"><?= $validationError->hasError('memory_limit.*') ? $validationError->getError('memory_limit.*') : '' ?></div>
<div class="shj_error"><?= $validationError->hasError('languages.*') ? $validationError->getError('languages.*') : '' ?></div>
<div class="shj_error"><?= $validationError->hasError('diff_cmd.*') ? $validationError->getError('diff_cmd.*') : '' ?></div>
<div class="shj_error"><?= $validationError->hasError('diff_arg.*') ? $validationError->getError('diff_arg.*') : '' ?></div>
<p class="input_p">
	<input type="submit" value="<?= $edit ? 'Edit' : 'Add' ?> Assignment" class="sharif_input"/>
</p>
</form>
<?php $this->endSection() ?>
