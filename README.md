# Jogo Educativo de Geolocalização

## Descrição

Este é um **jogo educativo** desenvolvido como trabalho da disciplina **Extensão 2**. O objetivo do jogo é que o jogador **encontre cidades aleatórias no mapa do mundo** usando pistas geográficas.  

O projeto utiliza **Laravel 12** no backend e **Leaflet.js** no frontend para exibir mapas interativos. Todo o **tracking de tempo e das cidades visitadas** é feito no backend, garantindo que o jogo funcione corretamente em qualquer navegador moderno.

---

## Tecnologias Utilizadas

- **Backend:** Laravel 12 (PHP)  
- **Frontend:** HTML, CSS, JavaScript, Leaflet.js  
- **Banco de dados:** MySQL (ou outro compatível com Laravel)  
- **Controle de versão:** Git/GitHub  

---

## Funcionalidades

- Geração aleatória de cidades para o jogador encontrar  
- Interface interativa com **mapa mundial** usando Leaflet.js  
- Tracking de tempo por rodada  
- Registro de cidades visitadas e pontuação no backend  

---

## Como Rodar o Projeto

1. Clone o repositório:

```bash

git clone https://github.com/Setanta01/trabEXT.git
cd trabEXT
Instale as dependências PHP e Node:

bash
Copiar código
composer install
npm install
Configure o arquivo .env:

bash
Copiar código
cp .env.example .env
php artisan key:generate
Ajuste as configurações de banco de dados no .env.

Rode as migrations:

bash
Copiar código
php artisan migrate
Inicie o servidor Laravel:

bash
Copiar código
php artisan serve
O projeto estará disponível em http://127.0.0.1:8000.

Como Jogar
Abra o jogo no navegador.

O sistema escolherá aleatoriamente uma cidade para você encontrar.

Navegue pelo mapa e tente localizar a cidade correta.

O tempo gasto e as cidades encontradas serão registrados no backend.
```

Autor
Setanta01 – Trabalho pessoal de faculdade (disciplina Extensão 2)
E-mail: leovanderleysmurf@gmail.com

Observações
Projeto somente para fins acadêmicos.

Funciona em qualquer navegador moderno.

Dependências externas, como Leaflet.js, estão inclusas via npm ou CDN.
