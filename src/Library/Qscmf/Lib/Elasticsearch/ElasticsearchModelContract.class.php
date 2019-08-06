<?php
namespace Qscmf\Lib\Elasticsearch;

interface ElasticsearchModelContract{

    function elasticsearchIndexList();

    function elasticsearchAddDataParams();

    function isElasticsearchIndex($ent);

    function createIndex();
}