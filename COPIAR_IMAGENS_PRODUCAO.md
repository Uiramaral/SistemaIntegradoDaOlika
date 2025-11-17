# 📸 Como Copiar Imagens de Produção para Desenvolvimento

## Problema Identificado

A pasta `storage/app/public/uploads/products/` está vazia no ambiente de desenvolvimento. As imagens dos produtos precisam ser copiadas de produção.

## Estrutura de Pastas

### Produção
```
/home4/hg6ddb59/public_html/producao/storage/app/public/uploads/products/
```

### Desenvolvimento
```
/home4/hg6ddb59/public_html/desenvolvimento/storage/app/public/uploads/products/
```

## Soluções

### Opção 1: Via FileZilla (Recomendado)

1. **Conecte-se ao servidor via FileZilla**

2. **Navegue até a pasta de produção:**
   ```
   /public_html/producao/storage/app/public/uploads/products/
   ```

3. **Selecione todas as imagens** (Ctrl+A ou Cmd+A)

4. **Copie para a pasta de desenvolvimento:**
   ```
   /public_html/desenvolvimento/storage/app/public/uploads/products/
   ```

5. **Aguarde o upload completar**

### Opção 2: Via cPanel File Manager

1. **Acesse o cPanel**

2. **Abra o File Manager**

3. **Navegue até:**
   ```
   public_html/producao/storage/app/public/uploads/products/
   ```

4. **Selecione todos os arquivos** (Ctrl+A)

5. **Clique em "Copy"**

6. **Cole em:**
   ```
   public_html/desenvolvimento/storage/app/public/uploads/products/
   ```

### Opção 3: Via SSH (se tiver acesso)

```bash
# Conectar ao servidor
ssh usuario@servidor

# Copiar todas as imagens
cp -r /home4/hg6ddb59/public_html/producao/storage/app/public/uploads/products/* \
      /home4/hg6ddb59/public_html/desenvolvimento/storage/app/public/uploads/products/

# Ajustar permissões
chmod -R 755 /home4/hg6ddb59/public_html/desenvolvimento/storage/app/public/uploads/products/
chmod -R 644 /home4/hg6ddb59/public_html/desenvolvimento/storage/app/public/uploads/products/*.jpg
chmod -R 644 /home4/hg6ddb59/public_html/desenvolvimento/storage/app/public/uploads/products/*.png
chmod -R 644 /home4/hg6ddb59/public_html/desenvolvimento/storage/app/public/uploads/products/*.webp
```

## Verificação

Após copiar, verifique:

1. **Acesse via FileZilla ou File Manager:**
   ```
   /public_html/desenvolvimento/storage/app/public/uploads/products/
   ```
   
   Deve conter as mesmas imagens que estão em produção.

2. **Teste acessar uma imagem diretamente:**
   ```
   https://devpedido.menuolika.com.br/storage/uploads/products/[nome-da-imagem].jpg
   ```
   
   Substitua `[nome-da-imagem]` por um nome real de imagem.

3. **Se funcionar:**
   - As imagens devem aparecer na página
   - O symlink está funcionando OU a rota `/storage/{path}` está servindo

## Nota Importante

- As imagens em `public/images/` (logo, placeholder, etc.) já estão corretas
- O problema é apenas com as imagens dos produtos em `storage/app/public/uploads/products/`
- Essas imagens são geradas quando produtos são criados/editados no dashboard

## Se Não Houver Imagens em Produção

Se a pasta de produção também estiver vazia ou quase vazia:

1. **As imagens podem estar sendo geradas dinamicamente**
2. **Ou podem estar em outro local**
3. **Verifique o banco de dados:**
   ```sql
   SELECT cover_image FROM products WHERE cover_image IS NOT NULL LIMIT 10;
   SELECT path FROM product_images LIMIT 10;
   ```
   
   Isso mostrará os caminhos das imagens no banco.

