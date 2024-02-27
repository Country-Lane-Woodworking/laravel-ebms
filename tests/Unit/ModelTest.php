<?php

namespace Mile6\LaravelEBMS\Tests\Unit;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Mile6\LaravelEBMS\Models\BaseModel;
use Mile6\LaravelEBMS\Models\Product;
use Mile6\LaravelEBMS\Tests\TestCase;

class ModelTest extends TestCase
{

    /** @test */
    function userCanSetTheSelectsForAModel()
    {
        $query = BaseModel::select(['ID', 'DESCR'])->getQueryUri();

        $this->assertEquals(http_build_query([
            '$select' => 'ID,DESCR'
        ]), $query);
    }

    /** @test */
    function userCanAddASelectForAModel()
    {
        $query = BaseModel::select(['ID', 'DESCR']);

        $this->assertEquals(http_build_query([
            '$select' => 'ID,DESCR'
        ]), $query->getQueryUri());

        $query->addSelect(['CUST_ID']);

        $this->assertEquals(http_build_query([
            '$select' => 'ID,DESCR,CUST_ID'
        ]), $query->getQueryUri());
    }

    /** @test */
    function userCanSetTheSelectsAsAStringForAModel()
    {
        $query = BaseModel::select('ID,DESCR')->getQueryUri();

        $this->assertEquals(http_build_query([
            '$select' => 'ID,DESCR'
        ]), $query);
    }

    /** @test */
    function userCanAddASelectAsAStringForAModel()
    {
        $query = BaseModel::select(['ID', 'DESCR']);

        $this->assertEquals(http_build_query([
            '$select' => 'ID,DESCR'
        ]), $query->getQueryUri());

        $query->addSelect('CUST_ID,CITY');

        $this->assertEquals(http_build_query([
            '$select' => 'ID,DESCR,CUST_ID,CITY'
        ]), $query->getQueryUri());
    }

    /** @test */
    function userCanSetASimpleExpandsForAModelUsingAStringValue()
    {
        $query = BaseModel::expand(['CUST_ID_reference' => 'CITY,NAME'])->getQueryUri();

        $this->assertEquals(http_build_query([
            '$expand' => 'CUST_ID_reference($select=CITY,NAME)'
        ]), $query);
    }

    /** @test */
    function userCanAddAnExpand()
    {
        $query = BaseModel::expand(['CUST_ID_reference' => 'CITY,NAME']);

        $this->assertEquals(http_build_query([
            '$expand' => 'CUST_ID_reference($select=CITY,NAME)'
        ]), $query->getQueryUri());

        $query->addExpand(['INV_ID_reference' => 'TOTAL,TAX']);

        $this->assertEquals(http_build_query([
            '$expand' => 'CUST_ID_reference($select=CITY,NAME),INV_ID_reference($select=TOTAL,TAX)'
        ]), $query->getQueryUri());

        $query->addExpand('PRODUCT_ID_reference', 'PRICE,DESCR');

        $this->assertEquals(http_build_query([
            '$expand' => 'CUST_ID_reference($select=CITY,NAME),INV_ID_reference($select=TOTAL,TAX),PRODUCT_ID_reference($select=PRICE,DESCR)'
        ]), $query->getQueryUri());
    }

    /** @test */
    function userCanSetAListOfSelectsForAnExpandsForAModelUsingANumericalArray()
    {
        $query = BaseModel::expand(['CUST_ID_reference' => ['CITY', 'NAME']])->getQueryUri();

        $this->assertEquals(http_build_query([
            '$expand' => 'CUST_ID_reference($select=CITY,NAME)'
        ]), $query);
    }

    /** @test */
    function userCanSetAListOfSelectsForMultipleExpandsForAModelUsingANumericalArray()
    {
        $query = BaseModel::expand(['CUST_ID_reference' => ['CITY', 'NAME'], 'INV_ID_reference' => ['TOTAL', 'TAX']])->getQueryUri();

        $this->assertEquals(http_build_query([
            '$expand' => 'CUST_ID_reference($select=CITY,NAME),INV_ID_reference($select=TOTAL,TAX)'
        ]), $query);
    }

    /** @test */
    function userCanPassACallableForAnExpandValue()
    {
        $query = BaseModel::expand(['CUST_ID_reference' => function ($query) {
            $query->select(['CITY', 'NAME']);
        }])->getQueryUri();

        $this->assertEquals(http_build_query([
            '$expand' => 'CUST_ID_reference($select=CITY,NAME)'
        ]), $query);
    }

    /** @test */
    function userCanSetAComplexExpandsUsingACallable()
    {
        $query = BaseModel::expand(['CUST_ID_reference' => function ($query) {
            $query->select(['CITY', 'NAME'])
                ->where('CITY', 'Lancaster');
        }])->getQueryUri();

        $this->assertEquals(http_build_query([
            '$expand' => "CUST_ID_reference(\$select=CITY,NAME&\$filter=CITY eq 'Lancaster')"
        ]), $query);
    }

    /** @test */
    function userCanSetAComplexExpandsForAModelUsingANumericalArray()
    {
        $query = BaseModel::expand(['CUST_ID_reference' => ['$select' => ['CITY', 'NAME'], '$filter' => ['CITY', '=', 'Lancaster']]])->getQueryUri();

        $this->assertEquals(http_build_query([
            '$expand' => "CUST_ID_reference(\$select=CITY,NAME&\$filter=CITY eq 'Lancaster')"
        ]), $query);
    }

    /** @test */
    function userCanCreateAQueryBlockUsingACallable()
    {
        $query = BaseModel::where(function ($query) {
            $query->where('CITY', 'Ephrata');
        })->getQueryUri();

        $this->assertEquals(http_build_query([
            '$filter' => "(CITY eq 'Ephrata')"
        ]), $query);
    }

    /** @test */
    function userCanCreateAQueryBlockUsingACallableWithOrWhere()
    {
        $query = BaseModel::where('NAME', 'Mile6')->orWhere(function ($query) {
            $query->where('CITY', 'Ephrata');
        })->getQueryUri();

        $this->assertEquals(http_build_query([
            '$filter' => "NAME eq 'Mile6' or (CITY eq 'Ephrata')"
        ]), $query);
    }

    /** @test */
    function userCanUseOrWhereByItselfWithoutProblems()
    {
        $query = BaseModel::orWhere('CITY', 'Ephrata')->getQueryUri();

        $this->assertEquals(http_build_query([
            '$filter' => "CITY eq 'Ephrata'"
        ]), $query);
    }

    /** @test */
    function userCanAddAnOrClauseWithOrWhere()
    {
        $query = BaseModel::where('CITY', 'Lancaster')->orWhere('CITY', 'Ephrata')->getQueryUri();

        $this->assertEquals(http_build_query([
            '$filter' => "CITY eq 'Lancaster' or CITY eq 'Ephrata'"
        ]), $query);
    }

    /** @test */
    function userCanAddMoreThanOneWhere()
    {
        $query = BaseModel::where('CITY', 'Elizabethtown')->where('NAME', 'Mile6')->getQueryUri();

        $this->assertEquals(http_build_query([
            '$filter' => "CITY eq 'Elizabethtown' and NAME eq 'Mile6'"
        ]), $query);
    }

    /** @test */
    function userCanAddAWhereInClause()
    {
        $query = BaseModel::whereIn('CITY', ['Elizabethtown', 'Lancaster'])->getQueryUri();

        $this->assertEquals(http_build_query([
            '$filter' => "CITY in ('Elizabethtown', 'Lancaster')"
        ]), $query);
    }

    /** @test */
    function userCanAddAnOrWhereInClause()
    {
        $query = BaseModel::where('NAME', 'Mile6')->orWhereIn('CITY', ['Elizabethtown', 'Lancaster'])->getQueryUri();

        $this->assertEquals(http_build_query([
            '$filter' => "NAME eq 'Mile6' or CITY in ('Elizabethtown', 'Lancaster')"
        ]), $query);
    }

    /** @test */
    function userCanAddAWhereInClauseWithPairs()
    {
        $query = BaseModel::whereIn(['FIRST_NAME', 'LAST_NAME'], [['John', 'Doe'], ['Jane', 'Doe']])->getQueryUri();

        $this->assertEquals(http_build_query([
            '$filter' => "[FIRST_NAME,LAST_NAME] in [['John','Doe'],['Jane','Doe']]"
        ]), $query);
    }

    /** @test */
    function userCanAddAnOrWhereInClauseWithPairs()
    {
        $query = BaseModel::where('NAME', 'Mile6')->orWhereIn(['FIRST_NAME', 'LAST_NAME'], [['John', 'Doe'], ['Jane', 'Doe']])->getQueryUri();

        $this->assertEquals(http_build_query([
            '$filter' => "NAME eq 'Mile6' or [FIRST_NAME,LAST_NAME] in [['John','Doe'],['Jane','Doe']]"
        ]), $query);
    }

    /** @test */
    function canSkipRecords()
    {
        $query = BaseModel::skip(5)->getQueryUri();

        $this->assertEquals(http_build_query([
            '$skip' => '5'
        ]), $query);
    }

    /** @test */
    function canLimitRecords()
    {
        $query = BaseModel::limit(5)->getQueryUri();

        $this->assertEquals(http_build_query([
            '$top' => '5'
        ]), $query);
    }

    /** @test */
    function canGetRecords()
    {
        Http::fake([
            '*' => Http::response([
                '@odata.context' => 'https://ecc702122005031401.servicebus.windows.net/MyEBMS/C-C/Odata/$metadata#INVENTRY',
                'value' => [[
                    'NAME' => 'Mile6'
                ]]
            ], 200)
        ]);

        $response = BaseModel::select('NAME')->where('NAME', 'Mile6')->get();

        Http::assertSent(function (Request $request) {
            return $request->url() === 'https://localhost/BASEMODEL/?' . http_build_query([
                    '$select' => 'NAME',
                    '$filter' => "NAME eq 'Mile6'"
                ]);
        });
    }

    /** @test */
    function canGetSpecificColumnsFromRecords()
    {
        Http::fake([
            '*' => Http::response([
                '@odata.context' => 'https://ecc702122005031401.servicebus.windows.net/MyEBMS/C-C/Odata/$metadata#INVENTRY',
                'value' => [[
                    'NAME' => 'Mile6',
                    'HELLO' => 'World'
                ]]
            ], 200)
        ]);

        $model = BaseModel::where('NAME', 'Mile6')->get()->first();

        $this->assertEquals('World', $model->HELLO);

        $model = BaseModel::where('NAME', 'Mile6')->get(['NAME'])->first();

        $this->assertNull($model->HELLO);
    }

    /** @test */
    function canAllRecords()
    {
        Http::fake([
            '*' => Http::response([
                '@odata.context' => 'https://ecc702122005031401.servicebus.windows.net/MyEBMS/C-C/Odata/$metadata#INVENTRY',
                'value' => [[
                    'NAME' => 'Mile6'
                ]]
            ], 200)
        ]);

        (new BaseModel())->newQuery()->all();

        Http::assertSent(function (Request $request) {
            return $request->url() === 'https://localhost/BASEMODEL';
        });
    }

    /** @test */
    function canCountRecords()
    {
        Http::fake([
            '*' => Http::response([
                '@odata.context' => 'https://ecc702122005031401.servicebus.windows.net/MyEBMS/C-C/Odata/$metadata#INVENTRY',
                '@odata.count' => 1,
                'value' => [[
                    'NAME' => 'Mile6'
                ]]
            ], 200)
        ]);

        (new BaseModel())->newQuery()->count();

        Http::assertSent(function (Request $request) {
            return $request->url() === 'https://localhost/BASEMODEL/?' . http_build_query([
                    '$top' => 0,
                    '$count' => 'true'
                ]);
        });
    }

    /** @test */
    function canGetAndSetAnAttributeFromARecord()
    {
        $model = new BaseModel([
            'name' => 'Alvin'
        ]);

        $this->assertEquals('Alvin', $model->name);

        $model->name = 'Theodore';

        $this->assertEquals('Theodore', $model->name);
    }

    /** @test */
    function fieldMappingToChangeTheMappedAttributeWhenGettingAnAttribute()
    {
        $model = new Product([
            'COST' => 1.00
        ]);

        $this->assertEquals(1, $model->COST);

        $this->assertEquals(1, $model->price);
    }

    /** @test */
    function fieldMappingToChangeTheMappedAttributeWhenSettingAnAttribute()
    {
        $model = new Product([
            'COST' => 1.00
        ]);

        $this->assertEquals(1, $model->COST);

        $model->price = 2;

        $this->assertEquals(2, $model->COST);

        $this->assertEquals(2, $model->price);
    }

    /** @test */
    function getAttributesByUpperCaseOrLowerCaseWhenGettingAnAttribute()
    {
        $model = new Product([
            'COST' => 1.00
        ]);

        $this->assertEquals(1, $model->COST);

        $this->assertEquals(1, $model->cost);
    }

    /** @test */
    function getAttributesByUpperCaseOrLowerCaseWhenSettingAnAttribute()
    {
        $model = new Product([
            'COST' => 1.00
        ]);

        $this->assertEquals(1, $model->COST);

        $model->cost = 2;

        $this->assertEquals(2, $model->cost);

        $this->assertEquals(2, $model->cost);
    }

    /** @test */
    function exceptionWillBeThrownTryingToWriteToAReadOnlyField()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("COUNT is a read only field");

        $model = new Product();

        $model->COUNT = 2;
    }

    /** @test */
    function exceptionWillBeThrownTryingToWriteToAReadOnlyFieldEvenWithFieldMapping()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("max_inventory is a read only field");

        $model = new Product();

        $model->max_inventory = 2;
    }

    /** @test */
    function exceptionWillBeThrownTryingToWriteToAReadOnlyFieldEvenWithCasing()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("count is a read only field");

        $model = new Product();

        $model->count = 2;
    }

    /** @test */
    function anInvalidOperatorResultsInException()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid Operator');

        $query = BaseModel::where('NAME', 'as', 'Mile6');
    }
}
