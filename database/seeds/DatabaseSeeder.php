<?php

class DatabaseSeeder extends AbstractSeeder
{

    protected function seed()
    {

        $this->call(CollectionsDatabaseSeeder::class);
        $this->call(ShopDatabaseSeeder::class);
        $this->call(MembershipDatabaseSeeder::class);
        $this->call(MobileDatabaseSeeder::class);
        $this->call(DscDatabaseSeeder::class);
        $this->call(StaticArchiveDatabaseSeeder::class);

    }

    protected static function unseed()
    {

        CollectionsDatabaseSeeder::clean();
        ShopDatabaseSeeder::clean();
        MembershipDatabaseSeeder::clean();
        MobileDatabaseSeeder::clean();
        DscDatabaseSeeder::clean();
        StaticArchiveDatabaseSeeder::clean();

    }

}
