README - Sistema Nhoc Report
1. Descrição do Projeto
O Nhoc Report é um sistema web simples para registro e acompanhamento diário de refeições e hidratação dos usuários Tina e Ana Laura.

O sistema permite:

Cadastro e login seguro por e-mail e senha

Registro de refeições diárias (tipo, horário, descrição de comida e bebida)

Registro diário da hidratação (meta 5 litros, incrementos de 500 ml)

Visualização de relatórios semanais com agrupamento por dia

Histórico semanal com navegação entre semanas

Exportação do relatório semanal em PDF para impressão

Layout responsivo e otimizado para PC e dispositivos móveis

Visual limpo, moderno e fácil de usar, baseado em Bootstrap 5

2. Estrutura do Projeto
Arquivo / Pasta	Descrição
index.php	Tela de login
dashboard.php	Painel principal com abas para refeições e hidratação
salvar_refeicao.php	Script para salvar as refeições enviadas pelo formulário
logout.php	Script para encerrar a sessão do usuário
relatorio.php	Página de relatório semanal para o usuário atual
historico.php	Página para visualizar relatórios de semanas anteriores com seleção por data
exportar_pdf.php	Gera o relatório semanal em PDF (requer biblioteca dompdf)
includes/db.php	Arquivo de configuração da conexão com o banco de dados MySQL
includes/dompdf/	Pasta contendo a biblioteca dompdf para exportação em PDF

3. Banco de Dados
Nome do banco: cervejac_nhocreport

Usuário MySQL: cervejac_nhocnhoc

Senha: HRI-^iiiz_v7

Tabelas principais
usuarios: Armazena usuários com senha criptografada (bcrypt)

refeicoes: Registros diários das refeições (tipo, horário, refeição, bebida)

agua: Registro diário da quantidade de água consumida em ml

4. Instalação e Configuração
Faça upload dos arquivos no diretório público da hospedagem (public_html no Hostgator)

Configure o banco de dados MySQL no cPanel da hospedagem, criando o banco e usuário com permissões

Importe o arquivo SQL (estrutura.sql) pelo phpMyAdmin para criar as tabelas e usuários iniciais

Ajuste o arquivo includes/db.php com as credenciais corretas do banco de dados

Acesse o sistema via navegador em https://seudominio.com.br/index.php

Faça login com os usuários:

Tina (email: tina@nhoc.com, senha: tinatinatin)

Ana Laura (email: ana@nhoc.com, senha: anaanaanaa)

Use o painel para registrar refeições, hidratação e visualizar relatórios

5. Uso das Funcionalidades
Login: Informe email e senha para acessar o sistema

Dashboard:

Aba Refeições: Cadastre refeições com data, horário, tipo, descrição da comida e bebida

Aba Hidratação: Clique no botão +500ml para registrar consumo diário, acompanhando a barra de progresso

Relatório Semanal (relatorio.php): Visualize a semana atual com refeições e consumo de água

Histórico (historico.php): Escolha qualquer data para visualizar o relatório da semana correspondente

Exportar PDF (exportar_pdf.php?data=YYYY-MM-DD): Exporte o relatório da semana para PDF pronto para impressão

6. Tecnologias Utilizadas
PHP 7+

MySQL

Bootstrap 5 (CSS e JS)

JavaScript básico

Biblioteca PHP dompdf para geração de PDF

7. Observações
O sistema foi desenvolvido para funcionar em hospedagens compartilhadas, como Hostgator

Senhas dos usuários já estão criptografadas e armazenadas no banco

O sistema é responsivo e deve funcionar bem em desktops, tablets e smartphones

Impressão do relatório otimizada para papel A4

8. Contato e Suporte
Em caso de dúvidas, ajustes ou melhorias, entre em contato com o desenvolvedor responsável.