# Docs - estrutura_php_puro_1.0

Abaixo está toda a documentação para entendimento da arquitetura e modelagem do código e padrão MVC que utilizamos.

## Start da aplicação

1. Execute: baixe o repositório em sua máquina.
2. Execute **composer update** para instalar as dependencias do projeto.
3. Configure seu servidor web para rodar o projeto a partir da pasta **public**.
4. Abra [App/Config.php](App/Config.php) para fazer as configurações gerais das credenciais.
5. A baixo está nossa arquitetura de pastas.
    1. App {
        1.1 Models
        1.2 Views
        1.3 Controllers
        1.4 Helpers
        1.5 Middlewares
        1.6 Traits
        1.7 Requests
        1.8 Config.php (global)
    2. Core {
        2.1 Helpers
        2.3 Services
        2.5 arquivos do core
    3. Logs
    4. Public (index do projeto)

Leia a baixo para mais detalhes

## Configuração

As definições de configuração são armazenadas na classe [App/Config.php](App/Config.php). As configurações padrão incluem dados de conexão do banco de dados e uma configuração para mostrar ou ocultar detalhes do erro. É possível adicionar novas configurações e chama-las dessa forma: `Config::DB_HOST`.

## Rotas

O [Router](Core/Router.php) traduz URLs em controladores e ações. As rotas são adicionadas na pasta [routes](routes), separadas por rotas `web` e rotas `api`. Uma rota inicial de amostra está incluída para direcionar para a ação `index` no [Controlador inicial](App/Controllers/Home.php).

As rotas são adicionadas com o método `add`. Você pode adicionar rotas de URL fixa e especificar o controlador e a ação, assim:

```php
$router->add('', ['controller' => 'Home', 'action' => 'index']);
$router->add('posts/index', ['controller' => 'Posts', 'action' => 'index']);
```

## Middlewares

```php
$router->add('posts/index', ['controller' => 'Posts', 'action' => 'index', 'middleware' => 'Auth']);
```

Você pode também passar parâmetros no middleware para tipos de permissão de usuários que deseja tratar, ex:

```php
$router->add('posts/index', ['controller' => 'Posts', 'action' => 'index', 'middleware' => 'Auth:admin']);
$router->add('posts/index', ['controller' => 'Posts', 'action' => 'index', 'middleware' => 'Auth:user']);
$router->add('posts/index', ['controller' => 'Posts', 'action' => 'index', 'middleware' => 'Auth:admin,user']);
```

Você pode adicionar **variáveis** de rota, assim:

```php
$router->add('{controller}/{action}');
```

Além do **controller** e **action**, você pode especificar qualquer parâmetro que desejar entre chaves e também especificar uma expressão regular personalizada para esse parâmetro:

```php
$router->add('{controller}/({id:\d+})/{action}'); // Apenas dígitos
$router->add('product/({slug:.+})/buy'); // Alfanúmericos
```

Você também pode especificar um namespace para o controlador:

```php
$router->add('admin/{controller}/{action}', ['namespace' => 'Admin']);
```

## Services

O [Services](Core/Services/) esse caminho é responsável por armazenar serviços externos utilizados em toda a aplicação. Serviços como CAF, Zenvia, ACERTPIX, entre outros... O namespace ficará assim para as chamadas dos services na aplicação [Services](App/Core/Services/Nome_do_service.php).

## Controllers

Os controladores respondem às ações do usuário (clicar em um link, enviar um formulário etc.). Os controladores são classes que estendem a classe [Core\Controller](Core/Controller.php).

Os controladores são armazenados na pasta `App/Controllers`. Um exemplo de [Controlador doméstico](App/Controllers/Home.php) incluído. As classes de controlador precisam estar no namespace `App/Controllers`. Você pode adicionar subdiretórios para organizar seus controladores, portanto, ao adicionar uma rota para esses controladores, você precisa especificar o namespace (consulte a seção de roteamento acima).

As classes de controlador contêm métodos que são as ações. Para criar uma ação, adicione o sufixo **`Action`** ao nome do método. O controlador de amostra em [App/Controllers/Home.php](App/Controllers/Home.php) tem uma ação `index` de amostra.

Você pode acessar os parâmetros de rota (por exemplo, o parâmetro **id** mostrado nos exemplos de rota acima) em ações através da propriedade `$this->route_params`.

### Action filters

Os controladores podem ter métodos de filtro **antes** e **depois**. Esses são métodos que são chamados antes e depois de **cada** chamada de método de ação em um controlador. Útil para autenticação, por exemplo, certificando-se de que um usuário esteja conectado antes de permitir que ele execute uma ação. Opcionalmente, adicione um **antes do filtro** a um controlador como este:

```php
protected function before()
{
}
```

Para interromper a execução da ação originalmente chamada, retorne `false` do método de filtro anterior. Um **before** é adicionado assim:

```php
/**
 * After filter.
 *
 * @return void
 */
protected function after()
{
}
```

## Views

As visualizações são usadas para exibir informações (normalmente HTML). Os arquivos de visualização vão para a pasta `App/Views`. As visualizações podem estar em um dos dois formatos: PHP padrão, mas com PHP suficiente para mostrar os dados. Nenhum acesso ao banco de dados ou algo parecido deve ocorrer em um arquivo de visualização. Você pode renderizar uma visualização padrão do PHP em um controlador, opcionalmente passando variáveis, como esta:

```php
View::render('Home/index.php', [
    'name'    => 'Dave',
    'colours' => ['red', 'green', 'blue']
]);
```

O segundo formato usa o mecanismo de modelagem [Twig](http://twig.sensiolabs.org/). Usar o Twig permite que você tenha modelos mais simples e seguros que podem tirar proveito de coisas como [herança de modelo](http://twig.sensiolabs.org/doc/templates.html#template-inheritance). Você pode renderizar um modelo Twig assim:

```php
View::renderTemplate('Home/index.html', [
    'name'    => 'Dave',
    'colours' => ['red', 'green', 'blue']
]);
```

Um modelo Twig de amostra está incluído em [App/Views/Home/index.html](App/Views/Home/index.html) que herda do modelo base em [App/Views/base.html](App/Views/ base.html).

## Models

As Models são usadas ​​para obter e armazenar dados em seu aplicativo. Eles não sabem nada sobre como esses dados devem ser apresentados nas visualizações. As models estendem a classe `Core\Model` e usam [PDO](http://php.net/manual/en/book.pdo.php) para acessar o banco de dados. Eles são armazenados na pasta `App/Models`. Uma classe de modelo de usuário de amostra está incluída em [App/Models/User.php](App/Models/User.php). Você pode obter a instância de conexão do banco de dados PDO assim:

```php
$db = static::getDB();
```

## Query builder

O query builder é usado como alternativa ao SQL puro, ao instânciar uma Model automaticamente você terá as funções de query builder abaixo:

*Seletores*
```php
$users = Users::where("id",10)
        ->where("id","!=",10)
        ->where($column,$value)
        ->where($column,"!=",$value)
        ->whereRaw($where,$bind=[])
        ->orWhere($column,$value)
        ->orWhereRaw($where,$bind=[])
        ->distinct($column)
        ->groupBy($column)
        ->orderBy($column, $order=null)
        ->limit($number)
        ->offset($number)
```

*Operadores*
```php
$users = Users::insert($values=[])
        ->update($values=[])
        ->delete()
```

*Capturadores*
```php
$users = Users::select($colums=[])
        ->join($table,$where1,$operator,$where2)
        ->leftJoin($table,$where1,$operator,$where2)
        ->first()
        ->get()
        ->count()
```

## Database Migrations

As migrations são como controle de versão para seu banco de dados, permitindo que sua equipe defina e compartilhe a definição do esquema de banco de dados do aplicativo. Se você já teve que dizer a um colega de equipe para adicionar manualmente uma coluna ao esquema de banco de dados local depois de obter suas alterações do controle de origem, você enfrentou o problema que as migrations de banco de dados resolvem.
As migrations se encontram na pasta `App/Migrations`

Comando para criar uma migration:
```php artisan make:migration "example-migration"```

Exemplo de migration:
```php

// Migration: create-table-tb-steps-user
class m20220803154505 extends \Core\Migrations {
	function up() {
		$table = $this->table("tb_steps_user");
		$table->increments("id");
		$table->foreign("lead_id_in100")->references("tb_in100.id")->onDelete("CASCADE")->nullable();
		$table->foreign("lead_id")->references("tb_leads.id")->onDelete("CASCADE")->nullable();
		$table->string("telefone");
		$table->longtext("message");
		$table->integer("id_fluxo");
		$table->integer("step");
		$table->string("channel")->nullable();
		$table->string("type")->nullable();
		$table->make();
	}
	function down() {
		self::dropTable("tb_steps_user");
	}
}
```

Lista de comandos para criação/alteração de tabelas:
```php
$table->increments("column");
$table->string("column");
$table->integer("column");
$table->biginteger("column");
$table->text("column");
$table->longtext("column");
$table->decimal("column", $size=15, $decimals=2);
$table->float("column");
$table->timestamp("column");
$table->foreign("product_id")->references("products.id")->onDelete("cascade")
```

Lista de comandos complementares:
```php
$table->string("column")->nullable();
$table->string("column")->unique();
$table->string("column")->default("value");
```

Lista de comandos para drop:
```php
self::dropTable("table_name");
self::dropColumn("table_name","column_name");
self::dropForeign("table_name","column_name");
```

Exemplos para criar e adicionar campos:
```php
$table = $this->table("tb_example");
$table->string("column")->nullable();
$table->alter();
```
```php
$table = $this->table("tb_example");
$table->string("column")->nullable();
$table->add();
```
`As migrations de adição e edição de colunas só comportam uma coluna por migration, em caso de mais campos use uma migration para cada campo.`
## Debug

Se precisar debugar qualquer região do código e ter o resultado em `Array` ou `Objeto`. É possível ultilizar a função:

```php
var $array = ['melão', 'banana', 'uva'];
dd($array);
```

## Erros

Se a configuração `SHOW_ERRORS` for definida como `true`, os detalhes completos do erro serão mostrados no navegador se ocorrer um erro ou exceção. Se estiver definido como `false`, uma mensagem genérica será mostrada usando o [App/Views/404.html](App/Views/404.html) ou [App/Views/500.html](App/Views/500 .html), dependendo do erro.

## Design Pattern

Usamos na aplicação os seguintes Desing Pattern: (Builders e Requests)

O `Builder` é fundamental para receber a request e construir todo o caminho para salvar o processo e despachar para o próximo fluxo.

O `Request` é fundamental para a injeção de dependência e reponsável pelo `Getters` e `Setters` de toda a estrutura do request vindo de fora.

## Functions Globals

Temos Funções globais que nos ajuda a chamar uma funcionalida de uma forma rápida. ex:

- Para chamar qualquer `Model`: 

```php
Models('Lead');
Models('Settings');
```

- Você pode também executar alguma query do Banco de dados. Ex:

```php
Models('Lead')::where('id', '=', $id)->first(); // lista apenas o primeiro
Models('Lead')::get(); // lista todos
```

Obs: Todas as funções globais estão nesse caminho: [Core/Core.php];

