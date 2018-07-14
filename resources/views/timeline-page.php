<?php use Fisharebest\Webtrees\Functions\FunctionsEdit; ?>
<?php use Fisharebest\Webtrees\I18N; ?>

<h2 class="wt-page-title">
	<?= $title ?>
</h2>

<form class="wt-page-options wt-page-options-timeline-chart d-print-none">
	<input type="hidden" name="route" value="timeline">
	<input type="hidden" name="ged" value="<?= e($tree->getName()) ?>">
	<input type="hidden" name="scale" value="<?= e($scale) ?>">

	<?php foreach ($individuals as $individual): ?>
	<input name="xrefs[]" type="hidden" value="<?= e($individual->getXref()) ?>">
	<?php endforeach ?>

	<div class="row form-group">
		<label class="col-sm-3 col-form-label wt-page-options-label" for="xref-add">
			<?= I18N::translate('Individual') ?>
		</label>
		<div class="col-sm-9 wt-page-options-value">
			<?= FunctionsEdit::formControlIndividual($tree, null, [
				'id'   => 'xref-add',
				'name' => 'xrefs[]',
			]) ?>
		</div>
	</div>

	<div class="row form-group">
		<div class="col-form-label col-sm-3 wt-page-options-label"></div>
		<div class="col-sm-9 wt-page-options-value">
			<input class="btn btn-primary" type="submit" value="<?= /* I18N: A button label. */
			I18N::translate('add') ?>">
			<a class="btn btn-secondary" href="<?= e(route('timeline', ['ged' => $tree->getName()])) ?>">
				<?= /* I18N: A button label. */ I18N::translate('reset') ?>
			</a>
		</div>
	</div>

	<div class="row form-group">
		<div class="col-form-label col-sm-3 wt-page-options-label"></div>
		<div class="col-sm-9 wt-page-options-value">
			<a href="<?= e($zoom_in_url) ?>" class="icon-zoomin" title="<?= I18N::translate('Zoom in') ?>"></a>
			<a href="<?= e($zoom_out_url) ?>" class="icon-zoomout" title="<?= I18N::translate('Zoom out') ?>"></a>
		</div>
	</div>
</form>

<div class="row my-4">
	<?php foreach ($individuals as $n => $individual): ?>
	<div class="col-md-6 col-lg-4 col-xl-3 person<?= $n % 6 ?>">
		<?= $individual->getSexImage('large'); ?>
		<a href="<?= e($individual->url()) ?>">
			<?= $individual->getFullName() ?>
			<?php if ($individual->getAddName() !== ''): ?>
				<br>
				<?= $individual->getAddName() ?>
			<?php endif ?>
		</a>
		<a href="<?= e($remove_urls[$individual->getXref()]) ?>">
			<?= I18N::translate('Remove individual') ?>
		</a>
		<?php if ($individual->getBirthDate()->isOK()): ?>
			<br>
			<label>
				<input type="checkbox" name="agebar<?= $n ?>" value="ON" onclick="$('#agebox<?= $n ?>').toggle();">
				<?= /* I18N: an age indicator, which can be dragged around the screen */ I18N::translate('Show an age cursor') ?>
			</label>
		<?php endif ?>
	</div>
	<?php endforeach ?>
</div>

<div class="wt-ajax-load wt-page-content wt-chart wt-timeline-chart" data-ajax-url="<?= e($chart_url) ?>"></div>
