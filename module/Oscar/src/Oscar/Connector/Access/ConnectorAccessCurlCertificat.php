<?php


namespace Oscar\Connector\Access;

use Oscar\Connector\ConnectorRepport;
use Oscar\Connector\IConnector;
use Oscar\Exception\ConnectorException;
use Oscar\Exception\OscarException;
use Oscar\Utils\PhpPolyfill;
use GuzzleHttp\Client;
/**
 * Utilisation de CURL avec certificat pour accéder aux données en HTTP.
 *
 * Class ConnectorAccessCurlCertificat
 * @package Oscar\Connector\Access
 */
class ConnectorAccessCurlCertificat implements IConnectorAccess
{
    /** @var IConnector */
    private $connector;

    /**
     * @var string FILE_CERTIFICAT_SSL_KEY
     * Constante contenant le path vers le fichier de la clef ssh au sein du fichier de configuration config/connectors/
     */
    const FILE_CERTIFICAT_SSL_KEY = 'file_certificat_ssl_key';
    /**
     * @var string FILE_CERTIFICAT_CERT
     * Constante contenant le path vers le fichier du certificat au sein du fichier de configuration config/connectors/
     */
    const FILE_CERTIFICAT_CERT = 'file_certificat_cert';
    /**
     * @var string FILE_CERTIFICAT_PASS
     * Constante contenant le pass pour clef et certificat au sein du fichier de configuration config/connectors/
     */
    const FILE_CERTIFICAT_PASS = 'file_certificat_pass';

    /**
     * ConnectorAccessCurlCertificat constructor.
     * @param IConnector $connector Connector qui va consommer l'accès aux données.
     * @param array $options tableau d'options avec url.
     */
    public function __construct(IConnector $connector, $options = [])
    {
        $this->connector = $connector;
        if( array_key_exists('url', $options) )
            $this->url = $options['url'];
    }

    public function getDatas( $url )
    {
        try {

            //Vérif params et vérif présence fichier clef ssh
            if( !$this->connector->hasParameter(self::FILE_CERTIFICAT_SSL_KEY) ){
                throw new ConnectorException("Le paramètre '%s' est requis.", self::FILE_CERTIFICAT_SSL_KEY);
            }

            $fileCertificat_ssl_key = $this->connector->getParameter(self::FILE_CERTIFICAT_SSL_KEY);
            if( !file_exists($fileCertificat_ssl_key) ){
                throw new ConnectorException("Le fichier '%s' n'existe pas.", $fileCertificat_ssl_key);
            }

            //Vérif params et vérif présence fichier certificat
            if( !$this->connector->hasParameter(self::FILE_CERTIFICAT_CERT) ){
                throw new ConnectorException("Le paramètre '%s' est requis.", self::FILE_CERTIFICAT_CERT);
            }

            $fileCertificat_cert = $this->connector->getParameter(self::FILE_CERTIFICAT_CERT);
            if( !file_exists($fileCertificat_cert) ){
                throw new ConnectorException("Le fichier '%s' n'existe pas.", $fileCertificat_cert);
            }

            //Vérif params pass
            if( !$this->connector->hasParameter(self::FILE_CERTIFICAT_PASS) ){
                throw new ConnectorException("Le paramètre '%s' est requis.", self::FILE_CERTIFICAT_PASS);
            }
            $fileCertificat_pass = $this->connector->getParameter(self::FILE_CERTIFICAT_PASS);

            // Guzzle client V.6
            $client = new Client();
            $request = $client->get(
                $url,
                [
                    'ssl_key' => [$fileCertificat_ssl_key, $fileCertificat_pass],
                    'cert' => [$fileCertificat_cert, $fileCertificat_pass],
                 ]
            );

            //Récup status request
            $statutCode = $request->getStatusCode();

            switch ($statutCode){
                case 200:
                    $responseApiBody = $request->getBody();
                    break;
                case 301:
                case 302:
                throw new ConnectorException("Le serveur a répondu une erreur redirection permanente ou temporaire :", $statutCode);
                case 401:
                    throw new ConnectorException("Le serveur a répondu une erreur statut authentification :", $statutCode);
                case 403:
                    throw new ConnectorException("Le serveur a répondu une erreur statut accès refusé :", $statutCode);
                case 404:
                    throw new ConnectorException("Le serveur a répondu une erreur statut page non trouvée :", $statutCode);
                case 500:
                case 503:
                throw new ConnectorException("Le serveur a répondu une erreur statut erreur serveur interne :", $statutCode);
                case 504:
                    throw new ConnectorException("Le serveur a répondu une erreur statut erreur le serveur n'a pas répondu :", $statutCode);
                default:
                    throw new ConnectorException("Le serveur a répondu une erreur statut :", $statutCode);
            }
            //Retourne le contenu sous forme de json du corps de la requête
            return PhpPolyfill::jsonDecode($responseApiBody);

        }catch (\Exception $e){
            $message = "Error dans la connection à l'api distante, erreur lors de l'éxécution du script :". $_SERVER['SCRIPT_FILENAME']
                . "\n Nom du script : ".$_SERVER['SCRIPT_NAME']
                . "\n Fournissez en copiant collant ces informations au technicien de l'application SVP !\n";
            $message.=$e->getMessage();
            throw new OscarException($message, $e->getCode());
        }
    }

    public function getConnector(): IConnector
    {
        return $this->getConnector();
    }

    public function getDataSingle($remoteId, $params = null)
    {
        return $this->getDatas($this->getConnector()->getPathSingle($remoteId));
    }

    public function getDataAll($params = null)
    {
        return $this->getDatas($this->getConnector()->getPathAll());
    }
}