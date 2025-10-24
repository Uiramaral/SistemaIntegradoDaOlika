# 🍞 Sistema de Cardápio Digital - Olika

Sistema completo de cardápio digital desenvolvido em Laravel para a Olika - Pães Artesanais.

## 🚀 Funcionalidades

### Para o Cliente
- ✅ Navegação pelo cardápio por categorias
- ✅ Busca de produtos
- ✅ Carrinho de compras interativo
- ✅ Checkout com dados pessoais
- ✅ Escolha entre retirada e entrega
- ✅ Integração com MercadoPago (PIX/Cartão)
- ✅ Confirmação de pedido
- ✅ Notificações via WhatsApp

### Para o Administrador
- ✅ API REST completa
- ✅ Webhooks para integrações
- ✅ Sistema de cupons
- ✅ Controle de status dos pedidos
- ✅ Integração com WhatsApp para automação

## 📋 Pré-requisitos

- PHP 8.1+
- Laravel 10.x
- MySQL 8.0+
- Composer

## 🛠️ Instalação

### 1. Clone o repositório
```bash
git clone <repository-url>
cd sistema-olika
```

### 2. Instale as dependências
```bash
composer install
```

### 3. Configure o ambiente
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configure o banco de dados
Edite o arquivo `.env` com suas configurações:
```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=olika_db
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha
```

### 5. Execute as migrations
```bash
php artisan migrate
```

### 6. Popule o banco com dados iniciais
```bash
php artisan db:seed
```

### 7. Configure as integrações
Edite o arquivo `.env` com suas chaves:
```env
# MercadoPago
MERCADOPAGO_ACCESS_TOKEN=seu_token
MERCADOPAGO_PUBLIC_KEY=sua_chave_publica
MERCADOPAGO_ENV=sandbox

# WhatsApp
WHATSAPP_API_URL=sua_api_whatsapp
WHATSAPP_API_KEY=sua_chave_whatsapp
```

## 🗄️ Estrutura do Banco de Dados

### Tabelas Principais
- `categories` - Categorias de produtos
- `products` - Produtos do cardápio
- `customers` - Clientes
- `orders` - Pedidos
- `order_items` - Itens dos pedidos
- `coupons` - Cupons de desconto
- `settings` - Configurações do sistema

### Relacionamentos
- Category → hasMany → Product
- Customer → hasMany → Order
- Order → hasMany → OrderItem
- Product → hasMany → OrderItem

## 🔗 API Endpoints

### Cardápio
- `GET /api/menu/categories` - Lista categorias
- `GET /api/menu/products` - Lista produtos
- `GET /api/menu/products/featured` - Produtos em destaque
- `GET /api/menu/products/search?q=termo` - Busca produtos
- `GET /api/menu/product/{id}` - Detalhes do produto

### Pedidos
- `GET /api/orders` - Lista pedidos (admin)
- `GET /api/orders/customer?phone=telefone` - Pedidos do cliente
- `GET /api/orders/{id}` - Detalhes do pedido
- `PUT /api/orders/{id}/status` - Atualiza status

### Webhooks
- `POST /api/webhooks/mercadopago` - Webhook MercadoPago
- `POST /api/webhooks/whatsapp` - Webhook WhatsApp

## 🎨 Frontend

### Tecnologias
- Blade Templates
- Tailwind CSS
- Alpine.js
- JavaScript Vanilla

### Páginas
- `/` - Cardápio principal
- `/menu/categoria/{id}` - Categoria específica
- `/menu/produto/{id}` - Detalhes do produto
- `/menu/buscar` - Busca de produtos
- `/cart` - Carrinho de compras
- `/checkout` - Finalização do pedido
- `/order/success/{id}` - Confirmação do pedido

## 🔧 Configurações

### MercadoPago
1. Crie uma conta no MercadoPago
2. Obtenha suas chaves de API
3. Configure no `.env`
4. Configure a URL de webhook: `https://seudominio.com/api/webhooks/mercadopago`

### WhatsApp
1. Configure sua API do WhatsApp
2. Configure no `.env`
3. Configure a URL de webhook: `https://seudominio.com/api/webhooks/whatsapp`

## 📱 Notificações WhatsApp

O sistema envia automaticamente:
- Confirmação do pedido
- Pedido pronto
- Pedido entregue
- Pedido cancelado

## 🎯 Próximos Passos

1. **Módulo de Receitas** - Integração futura
2. **Dashboard Admin** - Painel administrativo
3. **Relatórios** - Analytics de vendas
4. **App Mobile** - Aplicativo nativo

## 🤝 Contribuição

1. Fork o projeto
2. Crie uma branch para sua feature
3. Commit suas mudanças
4. Push para a branch
5. Abra um Pull Request

## 📄 Licença

Este projeto está sob a licença MIT. Veja o arquivo [LICENSE](LICENSE) para mais detalhes.

## 📞 Suporte

Para suporte, entre em contato:
- Email: contato@olika.com.br
- Telefone: (71) 98701-9420
- WhatsApp: [Clique aqui](https://wa.me/5571987019420)

---

Desenvolvido com ❤️ para a Olika - Pães Artesanais
