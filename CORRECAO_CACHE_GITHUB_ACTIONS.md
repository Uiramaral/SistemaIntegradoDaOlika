# 🔧 Correção: Erro de Cache no GitHub Actions

## ❌ Erro Identificado

```
Error: Some specified paths were not resolved, unable to cache dependencies.
```

Este erro ocorre quando o GitHub Actions tenta fazer cache do npm, mas não encontra o arquivo `package-lock.json` no caminho especificado.

---

## ✅ Solução Aplicada

Removida a configuração de cache dos workflows, pois:

1. O cache não é crítico para o funcionamento do deploy
2. O caminho relativo ao subdiretório estava causando problemas
3. O deploy funciona normalmente sem cache (apenas um pouco mais lento)

---

## 📝 Arquivos Corrigidos

### `deploy.yml` (Automático)
```yaml
- name: Setup Node.js
  uses: actions/setup-node@v4
  with:
    node-version: '20'
    # Cache removido - causa erro com subdiretórios
```

### `deploy-manual.yml` (Manual)
```yaml
- name: Setup Node.js
  uses: actions/setup-node@v4
  with:
    node-version: '20'
    # Cache removido - causa erro com subdiretórios
```

---

## 🔄 Próximos Passos

1. **Commit e push** das alterações
2. **Re-executar** o workflow no GitHub Actions
3. O deploy deve funcionar normalmente agora

---

## 💡 Se Quiser Reativar o Cache Futuramente

Para usar cache com subdiretórios no GitHub Actions, você precisaria:

1. **Opção 1**: Configurar o cache após o checkout e verificar o arquivo:
```yaml
- name: Setup Node.js
  uses: actions/setup-node@v4
  with:
    node-version: '20'
    cache: 'npm'
    cache-dependency-path: |
      olika-whatsapp-integration/package-lock.json
```

2. **Opção 2**: Usar uma action de cache manual:
```yaml
- name: Get npm cache directory
  id: npm-cache-dir-path
  shell: bash
  run: echo "dir=$(npm config get cache)" >> ${GITHUB_OUTPUT}

- name: Cache node modules
  uses: actions/cache@v3
  id: npm-cache
  with:
    path: ${{ steps.npm-cache-dir-path.outputs.dir }}
    key: ${{ runner.os }}-node-${{ hashFiles('olika-whatsapp-integration/package-lock.json') }}
    restore-keys: |
      ${{ runner.os }}-node-
```

Mas por enquanto, **recomendo manter sem cache** para evitar problemas.

---

**Correção aplicada! ✅**

