create database if not exists gerenciamento_de_condominio;
use gerenciamento_de_condominio;

create table if not exists sindico(
id_sindico int not null auto_increment,
    primary key (id_sindico),
    cpf varchar(255) not null unique,
    nome varchar(255) not null,
    senha varchar(255) not null,
    email varchar(255) not null
);

create table if not exists predios(
id_predio int not null auto_increment,
    primary key (id_predio),
    id_sindico int not null,
    nome varchar(255) not null,
    cep varchar(255) not null,
    cidade varchar(255) not null,
    bairro varchar(255) not null,
    rua varchar(255) not null,
    numero float not null,
    ativo ENUM('sim','não') DEFAULT 'sim',
    constraint fk_predios_sindico foreign key (id_sindico) references sindico (id_sindico)
);

create table if not exists contatos(
    id_contato int not null auto_increment,
    primary key (id_contato),
    id_predio int not null,
    nome varchar(255) not null,
    telefone varchar(255) not null,
    cep varchar(255) not null,
    cidade varchar(255) not null,
    bairro varchar(255) not null,
    rua varchar(255) not null,
    numero float not null,
    constraint fk_contatos_predios foreign key (id_predio) references predios (id_predio)
);

create table if not exists despesas(
    id_despesa int not null auto_increment,
    id_predio int not null,
    id_contato int,
    primary key (id_despesa),
    nome varchar(255) not null,
    valor_total decimal(10,2) not null,
    vencimento varchar(255) not null,
    foi_pago enum('sim','não') not null DEFAULT 'não',
    constraint fk_despesas_contatos foreign key (id_contato) references contatos (id_contato),
    constraint fk_despesas_predios foreign key (id_predio) references predios (id_predio)
);

create table if not exists morador(
    id_morador INT NOT NULL auto_increment,
    PRIMARY KEY (id_morador),
    nome VARCHAR(255) NOT NULL,
    telefone VARCHAR(255) NOT NULL,
    cpf VARCHAR(255) NOT NULL UNIQUE
);

create table if not exists apartamentos(
id_apartamento int not null auto_increment,
    id_predio int not null,
    id_morador int not null,
    primary key (id_apartamento),
    numero int not null,
    andar varchar(255) not null,
    ativo ENUM('sim','não') not null DEFAULT 'sim',
    constraint fk_apartamentos_predios foreign key (id_predio) references predios (id_predio),
    constraint fk_apartamentos_morador foreign key (id_morador) references morador (id_morador)
);

create table if not exists apartamento_despesas(
    id_apartamento_despesas int not null auto_increment,
    id_apartamento int not null,
    nome varchar(255) not null,
    valor_singular decimal(10,2) not null,
    foi_pago enum('sim','não') not null DEFAULT 'não',
    constraint fk_apartamentos foreign key (id_apartamento) references apartamentos (id_apartamento),
	primary key (id_apartamento_despesas)
);
select * from apartamento_despesas;

