<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 25/09/15 12:50
 *
 * @copyright Certic (c) 2015
 */
namespace Oscar\View\Helpers;

use Oscar\Utils\UnicaenDoctrinePaginator;
use Zend\View\Helper\AbstractHtmlElement;

class UnicaenDoctrinePaginatorHelper extends AbstractHtmlElement
{
    /** @var  UnicaenDoctrinePaginator */
    private $paginator;
    private $nbrPage;
    private $url;

    public function __invoke($paginator, $url = null, $nbrPage = 40)
    {
        $this->url = null === $url
            ? $this->getView()->url() . '?page=%s'
            : $url;

        $this->paginator = $paginator;
        $this->nbrPage = $nbrPage;

        return $this;
    }

    public function __toString()
    {
        return $this->render();
    }

    public function render()
    {
        static $_CACHE;
        if (null === $_CACHE) {
            $_CACHE = [];
        }
        $uid = spl_object_hash($this->paginator);
        if (!isset($_CACHE[$uid])) {
            ob_start();
            ?>
            <nav>
                <div class="text-center">
                    <?php if( $this->paginator->getCountPage() > 1 ): ?>
                    <div class="btn-group">

                        <a href="<?= strtr($this->url, ['page=%s' => 'page=1']) ?>"
                           aria-label="Previous" class="btn btn-default btn-xs">
                            <span aria-hidden="true">&laquo;</span>
                        </a>

                        <?php

                        $startPage = $this->paginator->getCurrentPage() - floor($this->nbrPage / 2);
                        $startPage = $startPage < 1 ? 1 : $startPage;
                        $endPage = $startPage + $this->nbrPage;
                        $endPage = $endPage > $this->paginator->getCountPage() ? $this->paginator->getCountPage() : $endPage;
                        for ($page = $startPage; $page <= $endPage; ++$page): $css = $page == $this->paginator->getCurrentPage() ? 'btn-primary active disabled' : 'btn-default';
                            ?>

                            <a href="<?= strtr($this->url, ['page=%s' => "page=$page"]) ?>"
                               class="btn <?= $css ?> btn-xs"><?= $page ?></a>
                        <?php endfor;
                        ?>
                        <a href="<?= strtr($this->url, ['page=%s' => 'page='.$this->paginator->getCountPage()]) ?>"
                           aria-label="Next" class="btn btn-xs btn-default">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </div>
                    <?php endif; ?>
                    <p>
                        <small><?= $this->paginator->count() ?> résultat(s) -
                            page <?= $this->paginator->getCurrentPage() ?>
                            sur <?= $this->paginator->getCountPage() ?></small>
                    </p>
                </div>
            </nav>
            <?php
            $_CACHE[$uid] = ob_get_clean();
        }

        return $_CACHE[$uid];
    }
}
