Modelo
=====

Um modelo é uma instância de [CModel] ou de suas classes derivadas. Modelos são utilizados 
para manter dados e suas regras de negócios relevantes.

Um modelo representa um objeto de dados único. Esse objeto pode ser um registro de uma tabela 
em um banco de dados ou um formulário com entradas de um usuário. Cada campo do objeto de 
dados é representado, no modelo, por um atributo. Cada atributo tem um rótulo e pode ser 
validado por um conjunto de regras.

O Yii implementa dois tipos de modelos: form model (modelo de formulário) e active record. 
Ambos estendem a mesma classe base [CModel].

Um form model é uma instância da classe [CFormModel]. Ele é utilizado para manter dados 
coletados a partir de entradas de usuários. Esse tipo de dado geralmente é coletado, utilizado 
e, então, descartado. Por exemplo, em uma página de login, podemos utilizar um form model para 
representar as informações de nome de usuário e senha inseridas pelo usuário. 
Para mais detalhes, consulte [Trabalhando com formulários](/doc/guide/form.model)

Active Record (AR) é um padrão de projeto utilizado para abstrair o acesso ao 
banco de dados de uma maneira orientada a objetos. Cada objeto AR é uma instância da 
classe [CActiveRecord], ou de suas classes derivadas, representando um registro de uma 
tabela em um banco de dados. Os campos do registro são representados por propriedades do 
objeto AR. Mais detalhes sobre AR podem ser encontrados em: [Active Record](/doc/guide/database.ar).

<div class="revision">$Id: basics.model.txt 162 2008-11-05 12:44:08Z weizhuo $</div>
