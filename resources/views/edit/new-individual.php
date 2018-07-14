<?php use Fisharebest\Webtrees\Auth; ?>
<?php use Fisharebest\Webtrees\Config; ?>
<?php use Fisharebest\Webtrees\Fact; ?>
<?php use Fisharebest\Webtrees\FontAwesome; ?>
<?php use Fisharebest\Webtrees\Functions\FunctionsEdit; ?>
<?php use Fisharebest\Webtrees\GedcomTag; ?>
<?php use Fisharebest\Webtrees\I18N; ?>
<?php use Fisharebest\Webtrees\SurnameTradition; ?>
<?php use Fisharebest\Webtrees\View; ?>

<?php
if ($individual !== null) {
	$xref       = $individual->getXref();
	$cancel_url = $individual->url();
} elseif ($family !== null) {
	$xref       = $family->getXref();
	$cancel_url = $family->url();
} else {
	$cancel_url = route('admin-trees');
	$xref       = 'new';
}

// Different cultures do surnames differently
$surname_tradition = SurnameTradition::create($tree->getPreference('SURNAME_TRADITION'));

if ($name_fact !== null) {
	// Editing an existing name
	$name_fact_id = $name_fact->getFactId();
	$namerec      = $name_fact->getGedcom();
	$name_fields  = [
		'NAME' => $name_fact->getValue(),
		'TYPE' => $name_fact->getAttribute('TYPE'),
		'NPFX' => $name_fact->getAttribute('NPFX'),
		'GIVN' => $name_fact->getAttribute('GIVN'),
		'NICK' => $name_fact->getAttribute('NICK'),
		'SPFX' => $name_fact->getAttribute('SPFX'),
		'SURN' => $name_fact->getAttribute('SURN'),
		'NSFX' => $name_fact->getAttribute('NSFX'),
	];

	// Populate any missing subfields from the NAME field
	$npfx_accept = implode('|', Config::namePrefixes());
	if (preg_match('/(((' . $npfx_accept . ')\.? +)*)([^\n\/"]*)("(.*)")? *\/(([a-z]{2,3} +)*)(.*)\/ *(.*)/i', $name_fields['NAME'], $name_bits)) {
		$name_fields['NPFX'] = $name_fields['NPFX'] ?: $name_bits[1];
		$name_fields['GIVN'] = $name_fields['GIVN'] ?: $name_bits[4];
		$name_fields['NICK'] = $name_fields['NICK'] ?: $name_bits[6];
		$name_fields['SPFX'] = $name_fields['SPFX'] ?: trim($name_bits[7]);
		$name_fields['SURN'] = $name_fields['SURN'] ?: preg_replace('~/[^/]*/~', ',', $name_bits[9]);
		$name_fields['NSFX'] = $name_fields['NSFX'] ?: $name_bits[10];
	}
} else {
	// Creating a new name
	$name_fact_id = '';
	$namerec      = '';
	$name_fields  = [
		'NAME' => '',
		'TYPE' => '',
		'NPFX' => '',
		'GIVN' => '',
		'NICK' => '',
		'SPFX' => '',
		'SURN' => '',
		'NSFX' => '',
	];

	// Inherit surname from parents, spouse or child
	if ($family) {
		$father = $family->getHusband();
		if ($father && $father->getFirstFact('NAME')) {
			$father_name = $father->getFirstFact('NAME')->getValue();
		} else {
			$father_name = '';
		}
		$mother = $family->getWife();
		if ($mother && $mother->getFirstFact('NAME')) {
			$mother_name = $mother->getFirstFact('NAME')->getValue();
		} else {
			$mother_name = '';
		}
	} else {
		$father      = null;
		$mother      = null;
		$father_name = '';
		$mother_name = '';
	}
	if ($individual && $individual->getFirstFact('NAME')) {
		$indi_name = $individual->getFirstFact('NAME')->getValue();
	} else {
		$indi_name = '';
	}

	switch ($nextaction) {
		case 'add_child_to_family_action':
			$name_fields = array_merge($name_fields, $surname_tradition->newChildNames($father_name, $mother_name, $gender));
			break;
		case 'add_child_to_individual_action':
			if ($individual->getSex() === 'F') {
				$name_fields = array_merge($name_fields, $surname_tradition->newChildNames('', $indi_name, $gender));
			} else {
				$name_fields = array_merge($name_fields, $surname_tradition->newChildNames($indi_name, '', $gender));
			}
			break;
		case 'add_parent_to_individual_action':
			$name_fields = array_merge($name_fields, $surname_tradition->newParentNames($indi_name, $gender));
			break;
		case 'add_spouse_to_family_action':
			if ($father) {
				$name_fields = array_merge($name_fields, $surname_tradition->newSpouseNames($father_name, $gender));
			} else {
				$name_fields = array_merge($name_fields, $surname_tradition->newSpouseNames($mother_name, $gender));
			}
			break;
		case 'add_spouse_to_individual_action':
			$name_fields = array_merge($name_fields, $surname_tradition->newSpouseNames($indi_name, $gender));
			break;
		case 'add_unlinked_indi_action':
		case 'update':
			if ($surname_tradition->hasSurnames()) {
				$name_fields['NAME'] = '//';
			}
			break;
	}
}

$bdm = ''; // used to copy '1 SOUR' to '2 SOUR' for BIRT DEAT MARR

?>
<h2 class="wt-page-title"><?= $title ?></h2>

<form method="post" onsubmit="return checkform();">
	<input type="hidden" name="ged" value="<?= e($tree->getName()) ?>">
	<input type="hidden" name="action" value="<?= e($nextaction) ?>">
	<input type="hidden" name="fact_id" value="<?= e($name_fact_id) ?>">
	<input type="hidden" name="xref" value="<?= e($xref) ?>">
	<input type="hidden" name="famtag" value="<?= e($famtag) ?>">
	<input type="hidden" name="gender" value="<?= $gender ?>">
	<?= csrf_field() ?>

	<?php if ($nextaction === 'add_child_to_family_action' || $nextaction === 'add_child_to_individual_action'): ?>
		<?= FunctionsEdit::addSimpleTag($tree, '0 PEDI') ?>
	<?php endif ?>

	<?php
	// First - standard name fields
	foreach ($name_fields as $tag => $value) {
		if (substr_compare($tag, '_', 0, 1) !== 0) {
			echo FunctionsEdit::addSimpleTag($tree, '0 ' . $tag . ' ' . $value, '', '', null, $individual);
		}
	}

	// Second - advanced name fields
	if ($surname_tradition->hasMarriedNames() || preg_match('/\n2 _MARNM /', $namerec)) {
		$adv_name_fields = ['_MARNM' => ''];
	} else {
		$adv_name_fields = [];
	}
	if (preg_match_all('/(' . WT_REGEX_TAG . ')/', $tree->getPreference('ADVANCED_NAME_FACTS'), $match)) {
		foreach ($match[1] as $tag) {
			// Ignore advanced facts that duplicate standard facts
			if (!in_array($tag, ['TYPE', 'NPFX', 'GIVN', 'NICK', 'SPFX', 'SURN', 'NSFX'])) {
				$adv_name_fields[$tag] = '';
			}
		}
	}

	foreach (array_keys($adv_name_fields) as $tag) {
		// Edit existing tags, grouped together
		if (preg_match_all('/2 ' . $tag . ' (.+)/', $namerec, $match)) {
			foreach ($match[1] as $value) {
				echo FunctionsEdit::addSimpleTag($tree, '2 ' . $tag . ' ' . $value, '', GedcomTag::getLabel('NAME:' . $tag, $individual));
				if ($tag === '_MARNM') {
					preg_match_all('/\/([^\/]*)\//', $value, $matches);
					echo FunctionsEdit::addSimpleTag($tree, '2 _MARNM_SURN ' . implode(',', $matches[1]));
				}
			}
		}
		// Allow a new tag to be entered
		if (!array_key_exists($tag, $name_fields)) {
			echo FunctionsEdit::addSimpleTag($tree, '0 ' . $tag, '', GedcomTag::getLabel('NAME:' . $tag, $individual));
			if ($tag === '_MARNM') {
				echo FunctionsEdit::addSimpleTag($tree, '0 _MARNM_SURN');
			}
		}
	}

	// Third - new/existing custom name fields
	foreach ($name_fields as $tag => $value) {
		if (substr_compare($tag, '_', 0, 1) === 0) {
			echo FunctionsEdit::addSimpleTag($tree, '0 ' . $tag . ' ' . $value);
			if ($tag === '_MARNM') {
				preg_match_all('/\/([^\/]*)\//', $value, $matches);
				echo FunctionsEdit::addSimpleTag($tree, '2 _MARNM_SURN ' . implode(',', $matches[1]));
			}
		}
	}

	// Fourth - SOUR, NOTE, _CUSTOM, etc.
	if ($namerec !== '') {
		$gedlines = explode("\n", $namerec); // -- find the number of lines in the record
		$fields   = explode(' ', $gedlines[0]);
		$glevel   = $fields[0];
		$level    = $glevel;
		$type     = $fields[1];
		$tags     = [];
		$i        = 0;
		do {
			if ($type !== 'TYPE' && !array_key_exists($type, $name_fields) && !array_key_exists($type, $adv_name_fields)) {
				$text = '';
				for ($j = 2; $j < count($fields); $j++) {
					if ($j > 2) {
						$text .= ' ';
					}
					$text .= $fields[$j];
				}
				while (($i + 1 < count($gedlines)) && (preg_match('/' . ($level + 1) . ' CONT ?(.*)/', $gedlines[$i + 1], $cmatch) > 0)) {
					$text .= "\n" . $cmatch[1];
					$i++;
				}
				echo FunctionsEdit::addSimpleTag($tree, $level . ' ' . $type . ' ' . $text);
			}
			$tags[] = $type;
			$i++;
			if (isset($gedlines[$i])) {
				$fields = explode(' ', $gedlines[$i]);
				$level  = $fields[0];
				if (isset($fields[1])) {
					$type = $fields[1];
				}
			}
		} while (($level > $glevel) && ($i < count($gedlines)));
	}

	// If we are adding a new individual, add the basic details
	if ($nextaction !== 'update') {
		echo '</table><br><table class="table wt-facts-table">';
		// 1 SEX
		if ($famtag === 'HUSB' || $gender === 'M') {
			echo FunctionsEdit::addSimpleTag($tree, '0 SEX M');
		} elseif ($famtag === 'WIFE' || $gender === 'F') {
			echo FunctionsEdit::addSimpleTag($tree, '0 SEX F');
		} else {
			echo FunctionsEdit::addSimpleTag($tree, '0 SEX U');
		}
		$bdm = 'BD';
		if (preg_match_all('/(' . WT_REGEX_TAG . ')/', $tree->getPreference('QUICK_REQUIRED_FACTS'), $matches)) {
			foreach ($matches[1] as $match) {
				if (!in_array($match, explode('|', WT_EVENTS_DEAT))) {
					FunctionsEdit::addSimpleTags($tree, $match);
				}
			}
		}
		//-- if adding a spouse add the option to add a marriage fact to the new family
		if ($nextaction === 'add_spouse_to_individual_action' || $nextaction === 'add_spouse_to_family_action') {
			$bdm .= 'M';
			if (preg_match_all('/(' . WT_REGEX_TAG . ')/', $tree->getPreference('QUICK_REQUIRED_FAMFACTS'), $matches)) {
				foreach ($matches[1] as $match) {
					FunctionsEdit::addSimpleTags($tree, $match);
				}
			}
		}
		if (preg_match_all('/(' . WT_REGEX_TAG . ')/', $tree->getPreference('QUICK_REQUIRED_FACTS'), $matches)) {
			foreach ($matches[1] as $match) {
				if (in_array($match, explode('|', WT_EVENTS_DEAT))) {
					FunctionsEdit::addSimpleTags($tree, $match);
				}
			}
		}
	}

	echo '</table>';
	if ($nextaction === 'update') {
		// GEDCOM 5.5.1 spec says NAME doesn’t get a OBJE
		echo view('cards/add-source-citation', [
			'level'          => 2,
			'full_citations' => $tree->getPreference('FULL_SOURCES'),
			'tree'           => $tree,
		]);
		echo view('cards/add-note', [
			'level' => 2,
			'tree' => $tree,
		]);
		echo view('cards/add-shared-note', [
			'level' => 2,
			'tree' => $tree,
		]);
		echo view('cards/add-restriction', [
			'level' => 2,
			'tree' => $tree,
		]);
	} else {
		echo view('cards/add-source-citation', [
			'bdm'                     => $bdm,
			'level'                   => 1,
			'full_citations'          => $tree->getPreference('FULL_SOURCES'),
			'prefer_level2_sources'   => $tree->getPreference('PREFER_LEVEL2_SOURCES'),
			'quick_required_facts'    => $tree->getPreference('QUICK_REQUIRED_FACTS'),
			'quick_required_famfacts' => $tree->getPreference('QUICK_REQUIRED_FAMFACTS'),
			'tree'                    => $tree,
		]);
		echo view('cards/add-note', [
			'level' => 1,
			'tree' => $tree,
		]);
		echo view('cards/add-shared-note', [
			'level' => 1,
			'tree' => $tree,
		]);
		echo view('cards/add-restriction', [
			'level' => 1,
			'tree' => $tree,
		]);
	}

	?>
	<div class="row form-group">
		<div class="col-sm-9 offset-sm-3">
			<button class="btn btn-primary" type="submit">
				<?= FontAwesome::decorativeIcon('save') ?>
				<?= /* I18N: A button label. */
				I18N::translate('save') ?>
			</button>
			<?php if (preg_match('/^add_(child|spouse|parent|unlinked_indi)/', $nextaction)): ?>

				<button class="btn btn-primary" type="submit" name="goto" value="<?= $xref ?>">
					<?= FontAwesome::decorativeIcon('save') ?>
					<?= /* I18N: A button label. */
					I18N::translate('go to new individual') ?>
				</button>
			<?php endif ?>
			<a class="btn btn-secondary" href="<?= e($cancel_url) ?>">
				<?= FontAwesome::decorativeIcon('cancel') ?>
				<?= /* I18N: A button label. */
				I18N::translate('cancel') ?>
			</a>

			<?php if ($name_fact instanceof Fact && (Auth::isAdmin() || $tree->getPreference('SHOW_GEDCOM_RECORD'))): ?>
				<a class="btn btn-link" href="<?= e(route('edit-raw-fact', ['xref' => $xref, 'fact_id' => $name_fact->getFactId(), 'ged' => $tree->getName()])) ?>">
					<?= I18N::translate('Edit the raw GEDCOM') ?>
				</a>
			<?php endif ?>
		</div>
	</div>
</form>

<?= view('modals/on-screen-keyboard') ?>
<?= view('modals/ajax') ?>
<?= view('edit/initialize-calendar-popup') ?>

<?php View::push('javascript') ?>
<script>
  var SURNAME_TRADITION = <?= json_encode($tree->getPreference('SURNAME_TRADITION')) ?>;
  var gender            = <?= json_encode($gender) ?>;
  var famtag            = <?= json_encode($famtag) ?>;

  var NAME = $("[name=NAME]");

  function trim(str) {
    str = str.replace(/\s\s+/g, " ");
    return str.replace(/(^\s+)|(\s+$)/g, "");
  }

  function lang_class(str) {
    if (str.match(/[\u0370-\u03FF]/)) return "greek";
    if (str.match(/[\u0400-\u04FF]/)) return "cyrillic";
    if (str.match(/[\u0590-\u05FF]/)) return "hebrew";
    if (str.match(/[\u0600-\u06FF]/)) return "arabic";
    return "latin"; // No matched text implies latin :-)
  }

  // Generate a full name from the name components
  function generate_name() {
    var npfx = $("[name=NPFX]").val();
    var givn = $("[name=GIVN]").val();
    var spfx = $("[name=SPFX]").val();
    var surn = $("[name=SURN]").val();
    var nsfx = $("[name=NSFX]").val();

    if (SURNAME_TRADITION === "polish" && (gender === "F" || famtag === "WIFE")) {
      surn = surn.replace(/ski$/, "ska");
      surn = surn.replace(/cki$/, "cka");
      surn = surn.replace(/dzki$/, "dzka");
      surn = surn.replace(/żki$/, "żka");
    }

    // Commas are used in the GIVN and SURN field to separate lists of surnames.
    // For example, to differentiate the two Spanish surnames from an English
    // double-barred name.
    // Commas *may* be used in other fields, and will form part of the NAME.
    var locale = document.documentElement.lang;
    if (locale === "vi" || locale === "hu") {
      // Default format: /SURN/ GIVN
      return trim(npfx + " /" + trim(spfx + " " + surn).replace(/ *, */g, " ") + "/ " + givn.replace(/ *, */g, " ") + " " + nsfx);
    } else if (locale === "zh-Hans" || locale === "zh-Hant") {
      // Default format: /SURN/GIVN
      return npfx + "/" + spfx + surn + "/" + givn + nsfx;
    } else {
      // Default format: GIVN /SURN/
      return trim(npfx + " " + givn.replace(/ *, */g, " ") + " /" + trim(spfx + " " + surn).replace(/ *, */g, " ") + "/ " + nsfx);
    }
  }

  // Update the NAME and _MARNM fields from the name components
  // and also display the value in read-only "gedcom" format.
  function updatewholename() {
    // Don’t update the name if the user manually changed it
    if (manualChange) {
      return;
    }

    var npfx = $("[name=NPFX]").val();
    var givn = $("[name=GIVN]").val();
    var spfx = $("[name=SPFX]").val();
    var surn = $("[name=SURN]").val();
    var nsfx = $("[name=NSFX]").val();
    var name = generate_name();

    var display_id = NAME.attr('id') + "_display";

    NAME.val(name);
    $("#" + display_id).text(name);
    // Married names inherit some NSFX values, but not these
    nsfx = nsfx.replace(/^(I|II|III|IV|V|VI|Junior|Jr\.?|Senior|Sr\.?)$/i, "");
    // Update _MARNM field from _MARNM_SURN field and display it
    // Be careful of mixing latin/hebrew/etc. character sets.
    var ip       = document.getElementsByTagName("input");
    var marnm_id = "";
    var romn     = "";
    var heb      = "";
    for (var i = 0; i < ip.length; i++) {
      var val = trim(ip[i].value);
      if (ip[i].id.indexOf("_HEB") === 0)
        heb = val;
      if (ip[i].id.indexOf("ROMN") === 0)
        romn = val;
      if (ip[i].id.indexOf("_MARNM") === 0) {
        if (ip[i].id.indexOf("_MARNM_SURN") === 0) {
          var msurn = "";
          if (val !== "") {
            var lc = lang_class(document.getElementById(ip[i].id).value);
            if (lang_class(name) === lc)
              msurn = trim(npfx + " " + givn + " /" + val + "/ " + nsfx);
            else if (lc === "hebrew")
              msurn = heb.replace(/\/.*\//, "/" + val + "/");
            else if (lang_class(romn) === lc)
              msurn = romn.replace(/\/.*\//, "/" + val + "/");
          }
          document.getElementById(marnm_id).value                  = msurn;
          document.getElementById(marnm_id + "_display").innerHTML = msurn;
        } else {
          marnm_id = ip[i].id;
        }
      }
    }
  }

  // Toggle the name editor fields between
  // <input type="hidden"> <span style="display:inline">
  // <input type="text">   <span style="display:none">

  var oldName = "";

  // Calls to generate_name() trigger an update - hence need to
  // set the manual change to true first. We are probably
  // listening to the wrong events on the input fields...
  var manualChange = generate_name() !== NAME.val();

  function convertHidden(eid) {
    var input1 = $("#" + eid);
    var input2 = $("#" + eid + "_display");
    // Note that IE does not allow us to change the type of an input, so we must create a new one.
    if (input1.attr("type") === "hidden") {
      input1.replaceWith(input1.clone().attr("type", "text"));
      input2.hide();
    } else {
      input1.replaceWith(input1.clone().attr("type", "hidden"));
      input2.show();
    }
  }

  /**
   * if the user manually changed the NAME field, then update the textual
   * HTML representation of it
   * If the value changed set manualChange to true so that changing
   * the other fields doesn’t change the NAME line
   */
  function updateTextName(eid) {
    var element = document.getElementById(eid);
    if (element) {
      if (element.value !== oldName) {
        manualChange = true;
      }
      var delement = document.getElementById(eid + "_display");
      if (delement) {
        delement.innerHTML = element.value;
      }
    }
  }

  function checkform() {
    var ip = document.getElementsByTagName("input");
    for (var i = 0; i < ip.length; i++) {
      // ADD slashes to _HEB and _AKA names
      if (ip[i].id.indexOf("_AKA") === 0 || ip[i].id.indexOf("_HEB") === 0 || ip[i].id.indexOf("ROMN") === 0)
        if (ip[i].value.indexOf("/") < 0 && ip[i].value !== "")
          ip[i].value = ip[i].value.replace(/([^\s]+)\s*$/, "/$1/");
      // Blank out temporary _MARNM_SURN
      if (ip[i].id.indexOf("_MARNM_SURN") === 0)
        ip[i].value = "";
      // Convert "xxx yyy" and "xxx y yyy" surnames to "xxx,yyy"
      if ((SURNAME_TRADITION === "spanish" || "SURNAME_TRADITION" === "portuguese") && ip[i].id.indexOf("SURN") === 0) {
        ip[i].value = document.forms[0].SURN.value.replace(/^\s*([^\s,]{2,})\s+([iIyY] +)?([^\s,]{2,})\s*$/, "$1,$3");
      }
    }
    return true;
  }

  // If the name isn’t initially formed from the components in a standard way,
  // then don’t automatically update it.
  if (NAME.val() !== generate_name() && NAME.val() !== "//") {
    convertHidden(NAME.attr("id"));
  }
</script>
<?php View::endpush() ?>

