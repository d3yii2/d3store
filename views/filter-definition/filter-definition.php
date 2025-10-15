<?php

use cornernote\returnurl\ReturnUrl;
use d3yii2\d3store\models\Data\ActionFilter;
use eaBlankonThema\widget\ThReturnButton;
use yii\helpers\Html;

/* @var $config ActionFilter[] */

$this->title = 'Darbību kartēšanas noteikumu konfigurācija';
$this->setPageHeader($this->title);

/**
 * Renders a list of stack IDs or placeholder
 * @param array $config
 */
$this->addPageButtons(ThReturnButton::widget(['backUrl' => ReturnUrl::getUrl()]));

$anyStackText = 'Jebkura noliktava un aile'
?>

<div class="action-mapping-index">
    <p class="lead">
        Šajā tabulā ir parādīts, kā pielāgotās darbības tiek kartētas, pamatojoties uz sākotnējām darbībām un kaudzes nosacījumiem.
    </p>

    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="thead-light">
            <tr>
                <th style="width: 20%;">Custom Action</th>
                <th style="width: 20%;">Base Actions</th>
                <th style="width: 20%;">Filter Stack To</th>
                <th style="width: 20%;">Filter Stack From</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($config as $actionFilter): ?>
                <tr>
                    <td>
                        <strong><?= Html::encode($actionFilter->label) ?></strong>
                        <div class="text-muted medium mt-1">
                            <?= Html::encode($actionFilter->description) ?>
                        </div>
                    </td>

                    <td class="text-center bg-light">
                        <?php if (!empty($actionFilter->filterBaseAction)): ?>
                            <code>
                        <?= Html::encode(implode(', ', $actionFilter->filterBaseAction)) ?>
                            </code>
                        <?php else: ?>
                            <span class="text-muted">–</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?= implode('<br>', $actionFilter->filterStackToList()) ?: $anyStackText ?>
                    </td>
                    <td>
                        <?= implode('<br>', $actionFilter->filterStackFromList()) ?: $anyStackText ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
