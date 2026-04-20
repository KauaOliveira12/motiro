# MOTIRÕ — Portal Institucional
> Tecnologia Para Todos | IFSP Campus Guarulhos | Desde 2026

---

## 📁 Estrutura do Projeto

```
motiro/
├── index.html          → Página principal (5 dobras)
├── quem-somos.html     → História + Ações de Extensão detalhadas
├── personas.html       → Perfis completos da equipe
├── contato.html        → Formulário de contato
├── css/
│   └── style.css       → Estilos compartilhados (todas as páginas)
├── assets/
│   └── logo.png        → Logo da Motirõ (COLOQUE AQUI A IMAGEM ORIGINAL)
└── php/
    ├── db_config.php   → Configuração do banco de dados
    ├── contato.php     → Processador PHP do formulário
    └── banco.sql       → Script SQL para criar as tabelas
```

---

## 🚀 Instruções de Instalação na Hospedagem

### 1. Banco de Dados (phpMyAdmin)

1. Acesse o phpMyAdmin com suas credenciais
2. Clique em "Novo" para criar um banco de dados
3. Nome: `motiro` | Collation: `utf8mb4_unicode_ci`
4. Clique em "Criar"
5. Selecione o banco `motiro` e clique em "SQL"
6. Cole o conteúdo do arquivo `php/banco.sql` e execute

### 2. Configuração PHP

Abra `php/db_config.php` e edite:

```php
define('DB_HOST',   'localhost');        // Geralmente localhost
define('DB_NOME',   'motiro');           // Nome do banco criado
define('DB_USUARIO','SEU_USUARIO');      // Seu login da hospedagem
define('DB_SENHA',  'SUA_SENHA');        // Sua senha da hospedagem

define('SCRUM_MASTER_EMAIL', 'ana.carolina@motiro.com.br'); // E-mail da SM
```

### 3. Upload dos Arquivos

Via FTP ou Gerenciador de Arquivos da hospedagem:
1. Faça upload de **toda a pasta `motiro/`** para o diretório `public_html/`
2. Certifique-se de que a pasta `assets/` contém o arquivo `logo.png`

### 4. Logo da Startup

- Copie a imagem `WhatsApp_Image_2026-03-29_at_19_57_51.jpeg` para `assets/logo.png`
- A logo aparecerá automaticamente no navbar, hero e favicon

---

## 🧩 Páginas

| Arquivo | Descrição |
|---|---|
| `index.html` | Página principal com hero, MVV, ações, equipe e localização |
| `quem-somos.html` | História da startup + ações de extensão detalhadas |
| `personas.html` | Perfis completos dos 5 integrantes da equipe |
| `contato.html` | Formulário com validação JS + envio via PHP + MySQL |

---

## 🛠 Tecnologias Utilizadas

- **HTML5** semântico (nav, section, main, article, aside, footer)
- **CSS3** com variáveis, Grid, Flexbox, animações e design responsivo
- **JavaScript** puro (validação de formulário, AJAX fetch, IntersectionObserver)
- **PHP 8+** com PDO para banco de dados e envio de e-mail
- **MySQL** para armazenar as mensagens de contato
- **Google Fonts** — Cormorant Garamond + DM Sans

---

## 🎨 Paleta de Cores

| Nome | Hex | Uso |
|---|---|---|
| Verde Primário | `#1B5E20` | Elementos principais, navbar |
| Verde Médio | `#2E7D32` | Botões, destaques |
| Verde Vivo | `#388E3C` | Ícones, links |
| Verde Claro | `#66BB6A` | Bordas ativas, badges |
| Verde Suave | `#E8F5E9` | Backgrounds de seções |
| Branco | `#FFFFFF` | Backgrounds de cards |
| Off-white | `#FAFDF8` | Background geral |

---

## 📧 Fluxo do Formulário de Contato

```
Usuário preenche o form
    ↓
Validação JavaScript (client-side)
    ↓
POST para php/contato.php via fetch()
    ↓
Validação PHP (server-side)
    ↓
Salva em mensagens_contato (MySQL)
    ↓
Envia e-mail HTML ao Scrum Master
    ↓
Loga resultado em log_emails (MySQL)
    ↓
Retorna JSON { sucesso: true/false }
    ↓
Exibe mensagem ao usuário
```

---

## 👥 Equipe (Conteúdo Genérico)

| Nome | Papel | E-mail |
|---|---|---|
| Ana Carolina Silva | Scrum Master | ana.carolina@motiro.com.br |
| Bruno Oliveira Santos | Dev Full Stack | bruno.oliveira@motiro.com.br |
| Carla Mendes | UX/UI Designer | carla.mendes@motiro.com.br |
| Diego Ferreira | Analista de Sistemas | diego.ferreira@motiro.com.br |
| Eduarda Lima | Marketing Digital | eduarda.lima@motiro.com.br |

> **Nota:** Substitua os nomes, fotos e e-mails pelos dados reais da equipe antes do lançamento.

---

*Projeto desenvolvido como parte do programa de curricularização de extensão do IFSP Campus Guarulhos.*
