<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 25/09/15 12:50
 *
 * @copyright Certic (c) 2015
 */
namespace Oscar\View\Helpers;

use Oscar\Utils\UnicaenDoctrinePaginator;
use Laminas\View\Helper\AbstractHtmlElement;

class UnicaenPaginatorHelper extends AbstractHtmlElement
{
    private int $totalItems;
    private int $currentPage;
    private int $nbrPerPage;
    private $url;

    public function __invoke(int $totalItems, int $currentPage, $nbrPerPage = 50, $url = null)
    {
        if( $url !== null ){
            $this->url = $url;
        } else {
            $baseURL = $_SERVER['REQUEST_URI'];
            if( str_contains($baseURL, 'page=') ){
                $baseURL = preg_replace('/page=\d*/', 'page=%s', $baseURL);
            } elseif (str_contains($baseURL, '?') ){
               $baseURL .= '&page=%s';
            } else {
                $baseURL .= '?page=%s';
            }
            $this->url = $baseURL;
        }

        $this->totalItems = $totalItems;
        $this->currentPage = $currentPage;
        $this->nbrPerPage = $nbrPerPage;
        return $this;
    }

    protected function getTotalPages():int{
        return ceil($this->totalItems/$this->nbrPerPage);
    }

    protected function getCurrentPage():int{
        return $this->currentPage;
    }

    protected function getTotalItems():int{
        return $this->totalItems;
    }

    public function __toString()
    {
        return $this->render();
    }



    public function render()
    {

            ob_start();
            ?>
            <nav>
                <div class="text-center">
                    <?php if( $this->getTotalPages() > 1 ): ?>
                    <div class="btn-group">

                        <?php if($this->currentPage > 2): ?>
                        <a href="<?= strtr($this->url, ['page=%s' => 'page=1']) ?>"
                           aria-label="Previous" class="btn btn-default btn-xs">
                            <span aria-hidden="true">
                                <<
                            </span>
                        </a>
                        <?php endif;?>
                        <?php if($this->currentPage > 1): ?>
                            <a href="<?= strtr($this->url, ['page=%s' => 'page='.$this->currentPage-1]) ?>"
                               aria-label="Previous" class="btn btn-default btn-xs">
                            <span aria-hidden="true">
                                <
                            </span>
                            </a>
                        <?php endif;?>


                        <?php
                        // Calcule des bornes

                        if( $this->getTotalPages() - $this->getCurrentPage() > 15 ){
                            $firstA = $this->getCurrentPage();
                            $lastA = $firstA + 5;
                            $pages = range($firstA,$lastA);

                            $middle = floor(($this->getTotalPages()-$this->getCurrentPage())/2);
                            $middleA = $middle-2;
                            if( $middleA <= $lastA ){
                                $middleA = $lastA+1;
                            } else {
                                $pages = array_merge($pages,['.']);
                            }
                            $middleB = $middleA+5;
                            $pages = array_merge($pages,range($middleA, $middleB));

                            $lastA = $this->getTotalPages()-5;
                            if($lastA <= $middleB){
                                $lastA = $middleB+1;
                            } else {
                                $pages = array_merge($pages,['.']);
                            }
                            $pages = array_merge($pages, range($lastA,$this->getTotalPages()));

                        } else {
                            if( $this->getTotalPages()-$this->getCurrentPage() - 15 < 1 ){
                                $pages = range(1, $this->getTotalPages());
                            } else {
                                $pages = range($this->getCurrentPage(),$this->getTotalPages());
                            }
                        }
                        ?>
                        <?php
                        foreach ($pages as $page):
                            $css = $page == $this->getCurrentPage() ? 'btn-primary active disabled' : 'btn-default';
                            ?>
                            <?php if($page == '.'): ?>
                            <a href="#"
                               class="btn <?= $css ?> btn-xs disabled">...</a>
                            <?php else: ?>
                            <a href="<?= strtr($this->url, ['page=%s' => "page=$page"]) ?>"
                               class="btn <?= $css ?> btn-xs"><?= $page ?></a>
                            <?php endif; ?>
                        <?php endforeach;
                        ?>

                    </div>
                    <?php endif; ?>
                    <p>
                        <small title="<?= $this->nbrPerPage ?> résultats par page">
                            <?= $this->getTotalItems() ?> résultat(s) -
                            page <?= $this->getCurrentPage() ?>
                            sur <?= $this->getTotalPages() ?>
                        </small>
                    </p>
                </div>
            </nav>
            <?php


        return ob_get_clean();
    }
}
