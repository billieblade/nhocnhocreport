-- Criação da tabela de usuários
CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  senha_hash VARCHAR(255) NOT NULL
);

-- Criação da tabela de refeições
CREATE TABLE refeicoes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL,
  data DATE NOT NULL,
  tipo ENUM('Café', 'Almoço', 'Lanche', 'Jantar', 'Extra') NOT NULL,
  horario TIME NOT NULL,
  refeicao TEXT,
  bebida TEXT,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Criação da tabela de hidratação (água)
CREATE TABLE agua (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL,
  data DATE NOT NULL,
  quantidade_ml INT NOT NULL,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Inserção dos dois usuários com senhas já criptografadas
INSERT INTO usuarios (nome, email, senha_hash)
VALUES
  ('Tina', 'tina@nhoc.com', '$2y$10$VMTpZoNq7HHJYkz6FgJG1eX0XNO9UGhOhTiZl.z8N6QUxNiwAi6AK'), -- senha: tinatinatin
  ('Ana Laura', 'ana@nhoc.com', '$2y$10$MTaW3DNxlhJeHp8zjKxGJutvGQTyPeul3iFvP9DcnFURKNIVbcfeK'); -- senha: anaanaanaa
