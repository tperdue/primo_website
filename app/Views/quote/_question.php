<?php
$questionId = (int) $question['id'];
$inputId = 'question-' . $questionId;
$answer = $answers[$questionId] ?? ($question['field_type'] === 'checkboxes' ? [] : '');
$answerValues = is_array($answer) ? array_map('strval', $answer) : [];
$requiredText = $question['is_required'] ? ' (required)' : '';
?>
<div class="quote-question">
    <?php if (in_array($question['field_type'], ['radio', 'checkboxes', 'yes_no'], true)): ?>
        <fieldset><legend><?= esc($question['label']) ?><span><?= esc($requiredText) ?></span></legend>
            <?php if ($question['help_text']): ?><p class="quote-question-help"><?= esc($question['help_text']) ?></p><?php endif ?>
            <div class="quote-options">
                <?php if ($question['field_type'] === 'yes_no'): ?>
                    <?php foreach (['yes' => 'Yes', 'no' => 'No'] as $value => $label): ?><label><input type="radio" name="answers[<?= $questionId ?>]" value="<?= $value ?>" <?= $answer === $value ? 'checked' : '' ?>> <span><?= $label ?></span></label><?php endforeach ?>
                <?php else: ?>
                    <?php foreach ($question['options'] as $option): ?><?php $optionId = (string) $option['id']; ?><label><input type="<?= $question['field_type'] === 'checkboxes' ? 'checkbox' : 'radio' ?>" name="answers[<?= $questionId ?>]<?= $question['field_type'] === 'checkboxes' ? '[]' : '' ?>" value="<?= (int) $option['id'] ?>" <?= ($question['field_type'] === 'checkboxes' ? in_array($optionId, $answerValues, true) : (string) $answer === $optionId) ? 'checked' : '' ?>> <span><?= esc($option['label']) ?></span></label><?php endforeach ?>
                <?php endif ?>
            </div>
        </fieldset>
    <?php elseif ($question['field_type'] === 'textarea'): ?>
        <label for="<?= $inputId ?>"><?= esc($question['label']) ?><span><?= esc($requiredText) ?></span></label>
        <?php if ($question['help_text']): ?><p class="quote-question-help"><?= esc($question['help_text']) ?></p><?php endif ?>
        <textarea id="<?= $inputId ?>" name="answers[<?= $questionId ?>]" rows="5" maxlength="5000" placeholder="<?= esc($question['placeholder'] ?? '', 'attr') ?>"><?= esc(is_string($answer) ? $answer : '') ?></textarea>
    <?php elseif ($question['field_type'] === 'select'): ?>
        <label for="<?= $inputId ?>"><?= esc($question['label']) ?><span><?= esc($requiredText) ?></span></label>
        <?php if ($question['help_text']): ?><p class="quote-question-help"><?= esc($question['help_text']) ?></p><?php endif ?>
        <select id="<?= $inputId ?>" name="answers[<?= $questionId ?>]"><option value=""><?= esc($question['placeholder'] ?: 'Choose an option') ?></option><?php foreach ($question['options'] as $option): ?><option value="<?= (int) $option['id'] ?>" <?= (string) $answer === (string) $option['id'] ? 'selected' : '' ?>><?= esc($option['label']) ?></option><?php endforeach ?></select>
    <?php elseif ($question['field_type'] === 'file'): ?>
        <span class="quote-question-label"><?= esc($question['label']) ?><span><?= esc($requiredText) ?></span></span>
        <?php if ($question['help_text']): ?><p class="quote-question-help"><?= esc($question['help_text']) ?></p><?php endif ?>
        <p class="quote-file-note">Supporting files can be attached before the request is sent.</p>
    <?php else: ?>
        <?php $htmlType = ['email' => 'email', 'phone' => 'tel', 'number' => 'number', 'date' => 'date'][$question['field_type']] ?? 'text'; ?>
        <label for="<?= $inputId ?>"><?= esc($question['label']) ?><span><?= esc($requiredText) ?></span></label>
        <?php if ($question['help_text']): ?><p class="quote-question-help"><?= esc($question['help_text']) ?></p><?php endif ?>
        <input id="<?= $inputId ?>" type="<?= $htmlType ?>" name="answers[<?= $questionId ?>]" maxlength="500" value="<?= esc(is_string($answer) ? $answer : '', 'attr') ?>" placeholder="<?= esc($question['placeholder'] ?? '', 'attr') ?>">
    <?php endif ?>
</div>
