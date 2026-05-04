# Luminix Laravel Permission Integration

Integração do pacote [Spatie Laravel Permission](https://github.com/spatie/laravel-permission) com o framework [Luminix Backend](https://github.com/luminix-cms/backend), adicionando validação automática, observadores e descoberta de modelos.

## Requisitos

- PHP 8.2+
- Laravel 11.x
- [`spatie/laravel-permission`](https://github.com/spatie/laravel-permission) ^6.24
- [`luminix/backend`](https://github.com/luminix-cms/backend)

## Instalação

```bash
composer require luminix/laravel-permission-integration
```

## Configuração

### 1. Registrar os modelos na configuração de permissões

Após instalar o pacote, você precisa informar ao Spatie Laravel Permission que deve utilizar os modelos fornecidos por este pacote. Publique o arquivo de configuração do Spatie (caso ainda não o tenha feito) e registre os modelos:

```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

Em seguida, edite o arquivo `config/permission.php` e altere a seção `models`:

```php
// config/permission.php

use Luminix\LaravelPermissionIntegration\Models\Permission;
use Luminix\LaravelPermissionIntegration\Models\Role;

return [

    'models' => [

        'permission' => Permission::class,

        'role' => Role::class,

    ],

    // ...restante da configuração
];
```

> **Importante:** sem este passo, o pacote não funcionará corretamente — os modelos padrão do Spatie serão utilizados no lugar dos modelos estendidos por esta integração.

### 2. Executar as migrações

```bash
php artisan migrate
```

## Funcionalidades

### Modelos estendidos

Os modelos `Role` e `Permission` estendem os modelos padrão do Spatie e adicionam:

- **Validação automática** via atributo PHP `#[WithValidator]`
- **Observadores** via atributo PHP `#[ObservedBy]`
- **Integração com o Luminix Backend** através da trait `LuminixModel`

#### Role

- Valida os campos `name`, `guard_name` e o array de `permissions` ao criar ou atualizar
- Sincroniza automaticamente as permissões associadas ao salvar (via `RoleObserver`)
- Eager-loading de permissões via escopo `scopeBeforeLuminix`

#### Permission

- Valida os campos `name` e `guard_name` ao criar ou atualizar

### Sincronização de permissões

Ao salvar uma função (role) com um array de permissões no corpo da requisição, o observador sincroniza automaticamente as permissões associadas:

> **Observação:** a associação de permissões só estará disponível para usuários que possuam autorização para `create-role` (ao criar) ou `update-role` (ao atualizar).

```json
POST /luminix-api/roles
{
    "name": "editor",
    "guard_name": "web",
    "permissions": ["create-post", "update-post"]
}
```

### Guards

O campo `guard_name` é validado contra os guards configurados em `config/auth.php`. Para acessar os guards disponíveis programaticamente:

```php
use Luminix\LaravelPermissionIntegration\Facades\Integration;

$guards = Integration::getAvailableGuards();
```

### Descoberta de modelos

O `PermissionServiceProvider` registra automaticamente os modelos `Role` e `Permission` no `ModelFinder` do Luminix Backend, tornando-os disponíveis para as operações CRUD geradas pelo framework.

## Uso com usuários

Este pacote é compatível com todos os recursos do Spatie Laravel Permission. Adicione a trait `HasRoles` ao seu model de usuário:

```php
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles;
}
```

Em seguida, utilize normalmente:

```php
$user->assignRole('editor');
$user->hasRole('editor');
$user->can('editar posts');
```

## Licença

MIT
