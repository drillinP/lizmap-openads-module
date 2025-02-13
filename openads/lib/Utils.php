<?php
/**
 * @author    3liz
 * @copyright 2021 3liz
 *
 * @see      http://3liz.com
 *
 * @license Mozilla Public License : http://www.mozilla.org/MPL/
 */

namespace openADS;

class Utils
{
    /**
     * Query database and return json data.
     *
     * @param string     $sql
     * @param null|array $params
     * @param string     $profile
     *
     * @return array
     */
    private function query($sql, $params = null, $profile = 'openads')
    {
        $cnx = \jDb::getConnection($profile);
        $cnx->beginTransaction();

        try {
            $resultset = $cnx->prepare($sql);
            $resultset->execute($params);
            $data = $resultset->fetchAll();
            $cnx->commit();
        } catch (\Exception $e) {
            $cnx->rollback();

            return array(
                'error',
                'A database error occured while executing the query',
                null,
            );
        }

        return array('success', 'Query executed with success', $data);
    }

    /**
     * Get a openads object.
     *
     * @param string     $key        The object to get. It corresponds to the table name. Ex: parcelles
     * @param mixed      $get_params Parameters needed for the get SQL
     * @param mixed      $schema
     * @param mixed      $profile
     * @param mixed      $method
     * @param mixed      $sql
     * @param mixed      $message
     * @param null|mixed $params
     *
     * @return array or null
     */
    public function execQuery($sql, $schema, $profile, $message, $params = null)
    {
        // Get object
        $sql = str_replace('!schema!', $schema, $sql);

        list($status, $msgError, $data) = $this->query($sql, $params, $profile);
        if ($status == 'error') {
            return array(
                'error',
                $msgError . ' ' . $message,
                null,
            );
        }

        return array(
            'success',
            '',
            $data,
        );
    }

    /**
     * Get profile from project and layer name.
     *
     * @param object $lizmap_project Lizmap Project
     * @param string $layerName      Layer Name
     *
     * @return string or null
     */
    public function getProfile($lizmap_project, $layerName)
    {
        // Get layer
        $qgisLayer = $this->getLayer($lizmap_project, $layerName);
        if (!$qgisLayer) {
            return null;
        }
        // get profile
        return $qgisLayer->getDatasourceProfile();
    }

    /**
     * Get layer from project and layer name.
     *
     * @param object $lizmap_project Lizmap Project
     * @param string $layerName      Layer Name
     *
     * @return object or null
     */
    public function getLayer($lizmap_project, $layerName)
    {
        if (!$lizmap_project) {
            return null;
        }
        $layer = $lizmap_project->findLayerByName($layerName);
        if (!$layer) {
            return null;
        }
        $layerId = $layer->id;
        $qgisLayer = $lizmap_project->getLayer($layerId);
        if (!$qgisLayer) {
            return null;
        }

        return $qgisLayer;
    }

    /**
     * Get schema from project and layer name.
     *
     * @param object $lizmap_project Lizmap Project
     * @param string $layerName      Layer Name
     *
     * @return string or null
     */
    public function getSchema($lizmap_project, $layerName)
    {
        // Check project layers and schema
        $schema = null;

        // Get layer
        $qgisLayer = $this->getLayer($lizmap_project, $layerName);
        if (!$qgisLayer) {
            return null;
        }

        $params = $qgisLayer->getDatasourceParameters();
        $schema = $params->schema;
        if (!$schema) {
            return null;
        }

        return $schema;
    }
}
