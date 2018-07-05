<?php

namespace Tests\Browser;

use D2EM;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;

use Entities\{
    VlanInterface       as VlanInterfaceEntity,
    Layer2Address       as Layer2AddressEntity
};

class Layer2AddressControllerTest extends DuskTestCase
{
    /**
     * Test the whole Interfaces functionalities (virtuel, physical, vlan)
     *
     * @return void
     *
     * @throws
     */
    public function testAddL2a()
    {

        shell_exec('bzcat data/travis-ci/travis_ci_test_db.sql.bz2| mysql -u root ixp_ci');

        $this->browse( function ( Browser $browser ) {

            $browser->resize(1600, 1200)
                ->visit('/auth/login')
                ->type('username', 'travis')
                ->type('password', 'travisci')
                ->press('submit')
                ->assertPathIs('/admin');

            // check that the vlan interface has no layer2address
            $browser->visit('/interfaces/virtual/edit/5')
                ->assertSee( "(none)" );

            // check DB
            /** @var VlanInterfaceEntity $vli */
            $this->assertInstanceOf(VlanInterfaceEntity::class, $vli = D2EM::getRepository(VlanInterfaceEntity::class)->find( 5 ) );

            // check that we have 0 layer2address for the vlan interface
            $this->assertEquals( 0, count( $vli->getLayer2Addresses() ) );

            // add mac address with wrong value
            $browser->visit('/layer2-address/vlan-interface/5')
                ->assertSee('Configured MAC Address Management')
                ->click( "#add-l2a" )
                ->waitForText( "Enter a MAC Address." )
                ->type( ".bootbox-input-text" , "bad-mac-address")
                ->press( "OK")
                ->waitUntilMissing( ".bootbox-prompt" )
                ->waitForText( "Configured MAC Address Management" )
                ->assertSee( "Invalid or missing MAC addresses" );

            // add mac address
            $browser->click( "#add-l2a" )
                ->waitForText( "Enter a MAC Address." )
                ->type( ".bootbox-input-text" , "e48d8c3521e5")
                ->press( "OK")
                ->waitUntilMissing( ".bootbox-prompt" )
                ->waitForText( "Configured MAC Address Management" )
                ->assertSee( "The MAC address has been added successfully." );


            // check DB
            D2EM::refresh( $vli );

            // check that we have 1 layer2address for the vlan interface
            $this->assertEquals( 1, count( $vli->getLayer2Addresses() ) );

            /** @var Layer2AddressEntity $l2a */
            $l2a = $vli->getLayer2Addresses()->first();

            $this->assertEquals("5", $l2a->getVlanInterface()->getId() );
            $this->assertEquals("e48d8c3521e5", $l2a->getMac() );


            // add same mac address as above
            $browser->click( "#add-l2a" )
                ->waitForText( "Enter a MAC Address." )
                ->type( ".bootbox-input-text" , "e48d8c3521e5")
                ->press( "OK")
                ->waitUntilMissing( ".bootbox-prompt" )
                ->waitForText( "Configured MAC Address Management" )
                ->assertSee( "The MAC address already exists within this" );

            // check that the vlan interface has the new layer2address
            $browser->visit('/interfaces/virtual/edit/5')
                ->assertSee( "e4:8d:8c:35:21:e5" );


            // go the the layer2address list
            $browser->visit('/layer2-address/vlan-interface/5')
                ->assertSee('Configured MAC Address Management');


            // check the mac address view popup
            $browser->element('.glyphicon-eye-open')->click();
            $browser->waitForText( "MAC Address" )
                ->assertInputValue( "#mac", "e48d8c3521e5" )
                ->press( "Close")
                ->waitForText( "Configured MAC Address Management" )
                ->waitForText( "e4:8d:8c:35:21:e5" );



            // add a second mac address
            $browser->click( "#add-l2a" )
                ->waitForText( "Enter a MAC Address." )
                ->type( ".bootbox-input-text" , "e48d8c3521e4")
                ->press( "OK")
                ->waitUntilMissing( ".bootbox-prompt" )
                ->waitForText( "Configured MAC Address Management" )
                ->assertSee( "The MAC address has been added successfully." );


            // check DB
            D2EM::refresh( $vli );

            // check that we have 2 layer2address for the vlan interface
            $this->assertEquals( 2, count( $vli->getLayer2Addresses() ) );

            /** @var Layer2AddressEntity $l2a */
            $l2a = $vli->getLayer2Addresses()->last();

            $this->assertEquals("5", $l2a->getVlanInterface()->getId() );
            $this->assertEquals("e48d8c3521e4", $l2a->getMac() );

            // check that the vlan interface has the new layer2address
            $browser->visit('/interfaces/virtual/edit/5')
                ->assertSee( "(multiple)" );


            // go the the layer2address list
            $browser->visit('/layer2-address/vlan-interface/5')
                ->assertSee('Configured MAC Address Management');


            foreach( $vli->getLayer2Addresses() as $l2a ){

                $l2aMac = $l2a->getMac();
                $browser->pause(2000);
                // delete mac addresses
                $browser->press('#delete-l2a-' . $l2a->getId() )
                    ->waitForText( 'Do you really want to delete this MAC Address?' )
                    ->press('Delete')
                    ->waitUntilMissing( ".bootbox-prompt" )
                    ->waitForText( "Configured MAC Address Management" );

                $this->assertEquals(null , D2EM::getRepository(Layer2AddressEntity::class)->findOneBy( [ "mac" => $l2aMac ] ) );

            }

            D2EM::refresh($vli);

            // check that we have 0 layer2address for the vlan interface
            $this->assertEquals( 0, count( $vli->getLayer2Addresses() ) );

            // check to add mac address as a USER (customer HEAnet)
            $browser->visit('/customer/overview/2/users')
                ->assertSee( "HEAnet" );





            // login as a USER (hecustuser)
            $browser->click( "#btn-login-as-4" )
                ->assertSee( "You are now logged in as hecustuser of HEAnet." );

            // visit layer2address list
            $browser->visit('/layer2-address/vlan-interface/2')
                ->assertSee( "MAC Address" );

            // add a mac address
            $browser->click( "#add-l2a" )
                ->waitForText( "Enter a MAC Address." )
                ->type( ".bootbox-input-text" , "e48d8c3521e1")
                ->press( "OK")
                ->waitUntilMissing( ".bootbox-prompt" )
                ->waitForText( "MAC Address Management" )
                ->waitForText( "The MAC address has been added successfully." );


            // check DB
            /** @var VlanInterfaceEntity $vli */
            $this->assertInstanceOf(VlanInterfaceEntity::class, $vli = D2EM::getRepository(VlanInterfaceEntity::class)->find( 2 ) );

            $this->assertEquals( 1, count( $vli->getLayer2Addresses() ) );

            /** @var Layer2AddressEntity $l2a */
            $this->assertInstanceOf(Layer2AddressEntity::class, $l2a = D2EM::getRepository(Layer2AddressEntity::class)->findOneBy( [ "mac" => "e48d8c3521e1" ] ) );

            $this->assertEquals("2", $l2a->getVlanInterface()->getId() );
            $this->assertEquals("e48d8c3521e1", $l2a->getMac() );

            // check that the delete button is not visible
            $browser->assertMissing( "#delete-l2a-" . $l2a->getId() );

            // add a second mac address
            $browser->click( "#add-l2a" )
                ->waitForText( "Enter a MAC Address." )
                ->type( ".bootbox-input-text" , "e48d8c3521e2")
                ->press( "OK")
                ->waitUntilMissing( ".bootbox-prompt" )
                ->waitForText( "MAC Address Management" )
                ->waitForText( "The MAC address has been added successfully." );

            D2EM::refresh( $vli );

            $this->assertEquals( 2, count( $vli->getLayer2Addresses() ) );

            /** @var Layer2AddressEntity $l2a2 */
            $this->assertInstanceOf(Layer2AddressEntity::class, $l2a2 = D2EM::getRepository(Layer2AddressEntity::class)->findOneBy( [ "mac" => "e48d8c3521e2" ] ) );

            $this->assertEquals("2", $l2a2->getVlanInterface()->getId() );
            $this->assertEquals("e48d8c3521e2", $l2a2->getMac() );

            $browser->waitFor( "#delete-l2a-" . $l2a->getId() );

            // check that the add button disapear and the delete buttons are available
            $browser->assertVisible( "#delete-l2a-" . $l2a->getId() );
            $browser->assertVisible( "#delete-l2a-" . $l2a2->getId());
            $browser->assertMissing( "#add-l2a");

            // delete the second mac address
            $browser->click( "#delete-l2a-" . $l2a2->getId() )
                ->waitForText( 'Do you really want to delete this MAC Address?' )
                ->press('Delete')
                ->waitUntilMissing( ".bootbox-prompt" )
                ->waitForText( "MAC Address Management" )
                ->waitForText( "The MAC address has been deleted." )
                ->waitUntilMissing( "#delete-l2a-" . $l2a2->getId() );

            // check DB
            D2EM::refresh( $vli );

            $this->assertEquals( 1, count( $vli->getLayer2Addresses() ) );

            $this->assertEquals(null , D2EM::getRepository(Layer2AddressEntity::class)->findOneBy( [ "mac" => "e48d8c3521e2" ] ) );

            // check that the add button disapear and the delete buttons are available
            $browser->assertMissing( "#delete-l2a-" . $l2a->getId() );
            $browser->assertVisible( "#add-l2a");

            $browser->quit();
        });


    }

}