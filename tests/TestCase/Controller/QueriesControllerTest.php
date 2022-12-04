<?php
declare( strict_types=1 );

namespace App\Test\TestCase\Controller;

use App\Controller\QueriesController;
use App\Model\Entity\ExperimentSite;
use App\Model\Entity\Query;
use App\Model\Entity\QueryGroup;
use App\Model\Table\QueriesTable;
use App\Test\TestCase\Controller\Shared\QueriesControllerTestTrait;
use App\Test\Util\AjaxTrait;
use App\Test\Util\AuthenticateTrait;
use App\Test\Util\DependsOnFixtureTrait;
use App\Test\Util\ExperimentSiteTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\QueriesController Test Case
 *
 * @uses \App\Controller\QueriesController
 */
class QueriesControllerTest extends TestCase {
    use IntegrationTestTrait;
    use DependsOnFixtureTrait;
    use AuthenticateTrait;
    use ExperimentSiteTrait;
    use AjaxTrait;
    use QueriesControllerTestTrait;

    private const ENDPOINT = '/queries';
    private const TABLE = 'Queries';
    private const CONTAINS = [
    ];

    protected array $dependsOnFixture = [
        'ExperimentSites',
        'QueryGroups',
        'Queries',
    ];
    protected QueriesTable $Table;

    protected function setUp(): void {
        $this->authenticate();
        $this->setSite();
        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->Table = $this->getTable( self::TABLE );
        parent::setUp();
    }

    /**
     * Test index method
     *
     * @return void
     */
    public function testIndex(): void {
        $this->addEntity();

        $this->get( self::ENDPOINT );

        $this->assertResponseSuccess();
        $this->assertResponseCode( 200 );

        $query = $this->Table
            ->find()
            ->all();

        /** @var Query $first */
        $first = $query->first();
        $last  = $query->last();

        $this->assertResponseContains( $first->code );
        $this->assertResponseContains( $last->code );
    }

    /**
     * Test view method
     *
     * @return void
     */
    public function testView(): void {
        $entity = $this->addEntity();

        $this->get( self::ENDPOINT . "/view/{$entity->id}" );

        $this->assertResponseSuccess();
        $this->assertResponseCode( 200 );

        $views = json_decode( $entity->my_query, true, 512, JSON_THROW_ON_ERROR )['fields'];

        $fields = [];
        foreach ( $views as $viewKey => $viewData ) {
            foreach ( $viewData as $fieldKey => $fieldValue ) {
                if ( is_numeric( $fieldValue ) && 1 === (int) $fieldValue ) {
                    $fields[] = "$viewKey.$fieldKey";
                }
            }
        }

        foreach ( $fields as $field ) {
            $this->assertResponseContains( $field );
        }

        /**
         * NOTE: Tests if the columns are present, but does not test the result
         * data!
         */
    }

    /**
     * Test viewMarkQuery method
     *
     * @return void
     */
    public function testViewMarkQuery(): void {
        $entity = $this->Table
            ->find()
            ->where(['my_query LIKE \'{"root_view":"MarksView"%\''])
            ->firstOrFail();

        $this->get( self::ENDPOINT . "/view-mark-query/" . $entity->id );

        $this->assertResponseSuccess();
        $this->assertResponseCode( 200 );

        $views = json_decode( $entity->my_query, true, 512, JSON_THROW_ON_ERROR )['fields'];

        $fields = [];
        foreach ( $views as $viewKey => $viewData ) {
            foreach ( $viewData as $fieldKey => $fieldValue ) {
                if ( 'MarkProperties' === $viewKey ) {
                    // not tested. todo: implement tests
                } else {
                    if ( 1 === (int) $fieldValue ) {
                        $fields[] = "$fieldKey";
                    }
                }
            }
        }

        foreach ( $fields as $field ) {
            $this->assertResponseContains( $field );
        }

        /**
         * NOTE: Tests if the columns are present, but does not test the result
         * data!
         */
    }

    /**
     * Test export method
     *
     * @return void
     */
    public function testExport(): void {
        $entity = $this->addEntity();

        $this->get( self::ENDPOINT . "/export/{$entity->id}" );

        $this->assertResponseSuccess();
        $this->assertResponseCode( 200 );
        $this->assertContentType( 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' );
        $this->assertHeaderContains( 'Content-Disposition', 'attachment' );
    }

    /**
     * Test add method
     *
     * @return void
     */
    public function testAdd(): void {
        $queryGroupsTable = $this->getTable( 'QueryGroups' );
        $queryGroup       = $queryGroupsTable->find()->firstOrFail();

        $data     = $this->getNonExistingEntityData();
        $my_query = json_decode( $data['my_query'], true, 512, JSON_THROW_ON_ERROR );

        $post_data = [
                         'code'           => $data['code'],
                         'description'    => $data['description'],
                         'query_group_id' => $data['query_group_id'],
                         'root_view'      => $my_query['root_view'],
                         'where_query'    => $my_query['where'],
                     ] + $my_query['fields'];

        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post( self::ENDPOINT . '/add/' . $queryGroup->id, $post_data );

        $this->assertResponseSuccess();
        $this->assertEntityExists( $data );
    }

    /**
     * Test edit method
     *
     * @return void
     */
    public function testEdit(): void {
        $entity = $this->addEntity();

        $my_query = json_decode( $entity->my_query, true, 512, JSON_THROW_ON_ERROR );

        $my_query['fields']['TreesView']['convar'] = '0';
        $my_query['fields']['TreesView']['offset'] = '1';

        $post_data = [
            'code'           => 'changed',
            'description'    => 'changed',
            'query_group_id' => $entity->query_group_id,
            'root_view'      => $my_query['root_view'],
            'where_query'    => $my_query['where'],
        ];

        $expected             = $post_data;
        $expected['my_query'] = json_encode( $my_query, JSON_THROW_ON_ERROR );
        unset( $expected['root_view'], $expected['where_query'] );

        $post_data += $my_query['fields'];

        $testEntity = $this->getEntityQueryFromArray( $post_data )
                           ->find( 'all', [ 'withDeleted' ] )
                           ->first();
        if ( $testEntity ) {
            $this->Table->hardDelete( $testEntity );
        }

        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post( self::ENDPOINT . '/add/' . $entity->query_group_id, $post_data );

        $this->assertResponseSuccess();
        $this->assertEntityExists( $expected );
    }

    /**
     * Test delete method
     *
     * @return void
     */
    public function testDelete(): void {
        $entity = $this->addEntity();

        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->delete( self::ENDPOINT . "/delete/{$entity->id}" );
        $this->assertResponseSuccess();

        $query = $this->getEntityQueryFromArray( $entity->toArray() );
        self::assertEquals( 0, $query->count() );
    }
}
