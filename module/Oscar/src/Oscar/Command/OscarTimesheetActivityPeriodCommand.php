<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 04/10/19
 * Time: 11:49
 */

namespace Oscar\Command;


use Moment\Moment;
use Oscar\Entity\Authentification;
use Oscar\Entity\LogActivity;
use Oscar\Entity\Person;
use Oscar\Entity\Role;
use Oscar\Entity\WorkPackage;
use Oscar\Entity\WorkPackagePerson;
use Oscar\Formatter\TimesheetActivityPeriodFormatter;
use Oscar\Service\ConnectorService;
use Oscar\Service\OscarConfigurationService;
use Oscar\Service\OscarUserContext;
use Oscar\Service\PersonService;
use Oscar\Service\TimesheetService;
use Oscar\Utils\DateTimeUtils;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Zend\Validator\Date;

class OscarTimesheetActivityPeriodCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'timesheets:activity-period-infos';

    const ARG_ACTIVITY      = "activity";
    const ARG_PERIOD        = "period";
    const ARG_FORMAT        = "format";

    protected function configure()
    {
        $this
            ->setDescription("Fourni des informations sur les informations de temps pour un déclarant à la période donnée")
            ->addArgument(self::ARG_ACTIVITY, InputArgument::REQUIRED, "login du déclarant")
            ->addArgument(self::ARG_PERIOD, InputArgument::REQUIRED, "periode sous la forme YYYY-MM")
            ->addArgument(self::ARG_FORMAT, InputArgument::REQUIRED, "format (pdf, xml, json, csv)")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        /// OPTIONS and PARAMETERS
        $activityId = $input->getArgument(self::ARG_ACTIVITY);
        $periodStr = $input->getArgument(self::ARG_PERIOD);
        $format = $input->getArgument(self::ARG_FORMAT);


        $serialize = false;

        $datas = $this->getTimesheetService()->getSynthesisActivityPeriod($activityId, $periodStr);

        if( $format == "xls" ) {
            $formatter = new TimesheetActivityPeriodFormatter();
            $formatter->output($datas);
        }

        return;

/****
        echo '
<?xml version="1.0"?>
<?mso-application progid="Excel.Sheet"?>
  <Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" 
            xmlns:o="urn:schemas-microsoft-com:office:office" 
            xmlns:x="urn:schemas-microsoft-com:office:excel" 
            xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" 
            xmlns:html="http://www.w3.org/TR/REC-html40">
    <DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">
      <Author>tbarbedo</Author>
      <Created>2009-05-29T18:21:48Z</Created>
      <Version>12.00</Version>
    </DocumentProperties>
    <ExcelWorkbook xmlns="urn:schemas-microsoft-com:office:excel">
      <WindowHeight>8895</WindowHeight>
      <WindowWidth>18015</WindowWidth>
      <WindowTopX>0</WindowTopX>
      <WindowTopY>105</WindowTopY>
      <ProtectStructure>False</ProtectStructure>
      <ProtectWindows>False</ProtectWindows>
    </ExcelWorkbook>
    <Styles>
      <Style ss:ID="Default" ss:Name="Normal">
        <Alignment ss:Vertical="Bottom"/>
        <Borders/><Font ss:FontName="Calibri" x:Family="Swiss" ss:Size="11" ss:Color="#000000"/>
        <Interior/>
        <NumberFormat/>
        <Protection/>
      </Style>
    </Styles>  
    
    <Worksheet ss:Name="Sheet1">
        <Table ss:ExpandedColumnCount="1" ss:ExpandedRowCount="5000" x:FullColumns="1" x:FullRows="1" ss:DefaultRowHeight="30">      
        ';

        $cols = [
            "A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z",
            "AA","AB","AC","AD","AE","AF","AG","AH","AI","AJ","AK","AL","AM","AN","AO","AP","AQ","AR","AS","AT","AU","AV","AW","AX","AY","AZ"
            ];
        $R = 1;
        $C = 1;

        echo '<Row>';
        echo '<Cell><Data ss:Type="String">Jours</Data></Cell>';
        foreach ($datas['daysInfos'] as $day=>$dayData) {
            echo '<Cell><Data ss:Type="String">' . $day . '</Data></Cell>';
        }
        echo '<Cell><Data ss:Type="String">TOTAL</Data></Cell>';
        echo '</Row>';

        $i=2;

        foreach ($datas['declarations'] as $itemKey=>$itemData) {

            foreach ($itemData as $subItem=>$subItemDatas) {
                $R++;
                echo '<Row>';
                echo '<Cell><Data ss:Type="String">' . $subItemDatas['acronym'] . ' ' . $subItemDatas['label'] . '</Data></Cell>';
                foreach ($datas['daysInfos'] as $day=>$dayData) {
                    echo '<Cell><Data ss:Type="Number"></Data></Cell>';
                }
                echo '<Cell><Data ss:Type="String">-</Data></Cell>';
                echo '</Row>';

                foreach ($subItemDatas['subgroup'] as $subGroupKey=>$subGroupdatas) {
                    $R++;
                    echo '<Row>';
                    echo '<Cell><Data ss:Type="String">'. $subGroupdatas['label'] .'</Data></Cell>';
                    $C = 1;
                    $colStart = $cols[$C+1];
                    for($i=1; $i<=$totalDays; $i++){
                        $C++;
                        $totalDay = 0;
                        if( array_key_exists($i, $subGroupdatas['days']) )
                            $totalDay = floatval($subGroupdatas   ['days'][$i]);
                        echo '<Cell><Data ss:Type="Number">'. $totalDay .'</Data></Cell>';

                    }
                    $colEnd = $cols[$C];
                    // ss:Formula="=SUM(R2C2:R2C3*R2C4:R2C5)"
                    $formula = "=SUM($colStart$R:$colEnd$R)";
                    echo '<Cell><Data ss:Formula="'.$formula.'"></Data></Cell>';
                    echo '</Row>';
                }
            }
        }

        echo '
        </Table>
        <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
        <PageSetup>
          <Header x:Margin="0.3"/>
          <Footer x:Margin="0.3"/>
          <PageMargins x:Bottom="0.75" x:Left="0.7" x:Right="0.7" x:Top="0.75"/>
        </PageSetup>
        <Selected/>
        <Panes>
          <Pane>
            <Number>3</Number>
            <ActiveRow>1</ActiveRow>
          </Pane>
        </Panes>
        <ProtectObjects>False</ProtectObjects>
        <ProtectScenarios>False</ProtectScenarios>
      </WorksheetOptions>
    </Worksheet>
  </Workbook>
        ';

        /****
        $io->title("Période '$periodStr' pour '$person' : ");
        dump($datas);

        $headers = [""];
        $rows = [];
        $totalDays = $datas['totalDays'];
        foreach ($datas['daysInfos'] as $day=>$dayData) {
            $headers[] = sprintf("%s%s", substr($dayData['label'], 0, 1), $day);
        }
        $headers[] = 'Total';

        foreach ($datas['declarations'] as $itemKey=>$itemData) {
            $rows[] = ["+++ $itemKey"];
            foreach ($itemData as $subItem=>$subItemDatas) {

                $rows[] = ['+' . $subItemDatas['acronym']];

                foreach ($subItemDatas['subgroup'] as $subGroupKey=>$subGroupdatas) {
                    $row = [substr($subGroupdatas['label'],0,7)];
                    for($i=1; $i<=$totalDays; $i++){
                        $totalDay = 0;
                        if( array_key_exists($i, $subGroupdatas['days']) )
                            $totalDay = floatval($subGroupdatas   ['days'][$i]);
                        if( $datas['daysInfos'][$i]['locked'] )
                            $totalDay = $totalDay == 0 ? "." : "!".$totalDay;
                        $row[] = $totalDay;
                    }
                    $row[] = $subGroupdatas['total'];
                    $rows[] = $row;
                    $rows[] = ['---'];
                }
                $rows[] = [" = " . $subItemDatas['total']];
            }
        }

        $rows[] = ["---"];
        $row = ["Actif"];
        for($i=1; $i<=$totalDays; $i++){
            $totalDay = 0;
            if( array_key_exists($i, $datas['active']['days']) )
                $totalDay = floatval($datas['active']['days'][$i]);
            if( $datas['daysInfos'][$i]['locked'] )
                $totalDay = $totalDay == 0 ? "." : "!".$totalDay;
            $row[] = $totalDay;
        }
        $row[] = $datas['active']['total'];
        $rows[] = $row;

        $io->table($headers, $rows);
        /******/
    }

    /**
     * @return TimesheetService
     */
    protected function getTimesheetService(){
        return $this->getServicemanager()->get(TimesheetService::class);
    }

    /**
     * @return PersonService
     */
    protected function getPersonService(){
        return $this->getServicemanager()->get(PersonService::class);
    }

    public function declarerPeriod( InputInterface $input, OutputInterface $output, $declarerId, $period ){
        // TODO Faire un rendu text des déclarations mensuelles des déclarants
        $datas = $this->getTimesheetService()->getTimesheetDatasPersonPeriod($this->getPersonService()->getPerson($declarerId), $period);
        echo "Non-disponible";
    }

    public function declarer( InputInterface $input, OutputInterface $output, $declarerId ){

        $io = new SymfonyStyle($input, $output);

        try {
            $declarer = $this->getPersonService()->getPerson($declarerId);

            $io->title("Système de relance pour $declarer");
            $periods = $this->getTimesheetService()->getPersonRecallDeclaration($declarer);

            $io->table(["Période", "Durée", "état"], $periods);

        } catch (\Exception $e) {
            $io->error('Impossible de charger le déclarant : ' . $e->getMessage());
            exit(0);
        }
    }

    public function declarersList( InputInterface $input, OutputInterface $output ){
        $io = new SymfonyStyle($input, $output);
        $io->title("Lite des déclarants");
        try {
            $declarants = $this->getTimesheetService()->getDeclarers();
            $out = [];
            /** @var Person $declarer */
            foreach ($declarants['persons'] as $personId=>$datas) {
                $out[] = [$personId, $datas['displayname'], $datas['affectation'], count($datas['declarations'])];
            }
            $headers = ['ID', 'Déclarant', 'Affectation', 'Déclaration(s)'];
            $io->table($headers, $out);

            $io->comment("Entrez la commande '".self::getName()." <ID> [PERIOD]' pour afficher les détails");
        } catch (\Exception $e) {
            $io->error($e->getMessage());
        }
    }
}