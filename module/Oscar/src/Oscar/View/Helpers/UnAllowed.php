<?php

namespace Oscar\View\Helpers;

use Laminas\View\Helper\AbstractHtmlElement;

/**
 * Description of unAllowed.
 *
 * @author Stéphane Bouvry <stephane.bouvry@unicaen.fr>
 */
class UnAllowed extends AbstractHtmlElement
{
    public function __invoke()
    {
        return $this->render();
    }

    public function __toString()
    {
        return $this->render();
    }

    public function render()
    {
        ob_start();
        ?>
<div class="alert alert-info">
    <i class="icon-lock"></i>
    Droits insuffisants pour voir ces informations.
</div>
    <?php
        return ob_get_clean();
    }
}
