--# ADDING LANGUAGES

INSERT INTO tb_languages ( language_codeset, language_code, language_wui, language_name ) VALUES 
  ( '1033', 'en-us', true, '"1033"=>"English (United States)", "1046"=>"Inglês (Estados Unidos)"' ),
  ( '1046', 'pt-br', true, '"1033"=>"Portuguese (Brazil)", "1046"=>"Português (Brasil)"' );
  
--# ADDING COMMON SENTENCES
SELECT insert_sentences ( 'CLICK_TO_EXPAND_OR_COLLAPSE', '"1033"=>"Click here to expand or collapse content", "1046"=>"Clique aqui para expandir ou comprimir o conteúdo"'::hstore );
SELECT insert_sentences ( 'TASK_ADDED', '"1033"=>"the task was created", "1046"=>"a tarefa foi criada"'::hstore );
SELECT insert_sentences ( 'ERROR_OCCURRED', '"1033"=>"an error has occurred", "1046"=>"ocorreu um erro"'::hstore );
SELECT insert_sentences ( 'LINKS', '"1033"=>"Links", "1046"=>"Links"'::hstore );
SELECT insert_sentences ( 'FETCH', '"1033"=>"Fetch", "1046"=>"Baixar"'::hstore );
SELECT insert_sentences ( 'CLEAR', '"1033"=>"Clear", "1046"=>"Limpar"'::hstore );
SELECT insert_sentences ( 'NETWORK_BLOCKED', '"1033"=>"The network you are accessing from was blocked for security reasons", "1046"=>"A rede de onde você está acessando foi bloqueada por razões de segurança"'::hstore );
SELECT insert_sentences ( 'PERMISSION_DENIED', '"1033"=>"You do not permission to view this page. ", "1046"=>"Você não tem permissão para ver esta página."'::hstore );

SELECT insert_sentences ( 'ERROR_BLANK_CONTACT_INFO', '"1033"=>"Please provide at least one contact information.", "1046"=>"Por favor, informe pelo menos uma forma de contato"'::hstore );
SELECT insert_sentences ( 'ERROR_INVALID_EMAIL', '"1033"=>"The e-mail you provided is invalid", "1046"=>"O e-mail informado é inválido"'::hstore );
SELECT insert_sentences ( 'ERROR_BLANK_MESSAGE', '"1033"=>"Message is blank", "1046"=>"A mensagem está em branco"'::hstore );
SELECT insert_sentences ( 'MESSAGE_SENT', '"1033"=>"Your message has been sent", "1046"=>"Sua mensagem foi enviada"'::hstore );
SELECT insert_sentences ( 'INVALID_LOGIN', '"1033"=>"The account information is invalid, please be nice and fill it correctly.", "1046"=>"As informações de conta são inválidas, por favor seja gentil e preencha corretamente."'::hstore );
SELECT insert_sentences ( 'INVALID_TOKEN', '"1033"=>"The token for linking account is not valid", "1046"=>"O token para vincular conta não é válido."'::hstore );

SELECT insert_sentences ( 'NEW_PASSWORD_HAS_BEEN_SENT', '"1033"=>"A new passwors has been sent to your e-mail", "1046"=>"Uma nova senha foi enviada ao seu e-mail"'::hstore );

SELECT insert_sentences ( 'AMMOUNT_ADDED', '"1033"=>"You have added [[-AMMOUNT-]] to your account", "1046"=>"Você adicionou [[-AMMOUNT-]] em sua conta."'::hstore );
SELECT insert_sentences ( 'SERVICE_PAID', '"1033"=>"You have paid [[-AMMOUNT-]] for the service [[-SERVICE-]]", "1046"=>"Você pagou [[-AMMOUNT-]] pelo serviço [[-SERVICE-]]"'::hstore );

--# ADDING PAGE SENTENCES AND SENTENCES
SELECT insert_sentences_page( 'NEW_POST', 'POSTS', '"1033"=>"New Post", "1046"=>"Nova Publicação"'::hstore );
SELECT insert_sentences_page( 'SCREEN_TOO_NARROW', 'POSTS', '"1033"=>"The screen is to narrow to use this feature", "1046"=>"A tela é muito estreita para o modo de edição"'::hstore );

SELECT insert_sentences_page( 'IP_ADDRESS', 'BOTTOM', '"1033"=>"IP Address", "1046"=>"Endereço IP"'::hstore );
SELECT insert_sentences_page( 'EXTERNAL_NETWORK', 'BOTTOM', '"1033"=>"External Network", "1046"=>"Rede Externa"'::hstore );
SELECT insert_sentences_page( 'INTERNAL_NETWORK', 'BOTTOM', '"1033"=>"Internal Network", "1046"=>"Rede Interna"'::hstore );
SELECT insert_sentences_page( 'TRUSTED_NETWORK', 'BOTTOM', '"1033"=>"Trusted Network", "1046"=>"Rede confiável"'::hstore );
SELECT insert_sentences_page( 'IP_ADDRESS', 'BOTTOM', '"1033"=>"IP Address", "1046"=>"Endereço IP"'::hstore );
SELECT insert_sentences_page( 'IP_ADDRESS', 'BOTTOM', '"1033"=>"IP Address", "1046"=>"Endereço IP"'::hstore );
SELECT insert_sentences_page( 'ALL_RIGHTS_RESERVED', 'BOTTOM', '"1033"=>"All rights reserved", "1046"=>"Todos os direitos reservados."'::hstore );
SELECT insert_sentences_page( 'DEVELOPED', 'BOTTOM', '"1033"=>"Developed by <a class=\"iglow4\" target=\"_blank\" href=\"https://zxe.com.br\">ZXE - Smart IT Solutions</a>", "1046"=>"Desenvolvido por <a target=\"_blank\" class=\"iglow4\" href=\"https://zxe.com.br\">ZXE - Soluções Inteligentes em TI</a>"'::hstore );
SELECT insert_sentences_page( 'POWERED_BY', 'BOTTOM', '"1033"=>"Powered by <a class=\"iglow4\" target=\"_blank\" href=\"https://zxe.com.br\">ZXE - being smart</a>", "1046"=>"Powered by <a target=\"_blank\" class=\"iglow4\" href=\"https://zxe.com.br\">ZXE - being smart</a>"'::hstore );

SELECT insert_sentences_page( 'USERNAME_HELP', 'LINK_ACCOUNT', '"1033"=>"The username is used for authentication and it is also how other users will identify you in your posts and comments. It must be at least 3 characters long and do not exceed 16 characters, containing only lowercase letters and numbers.","1046"=>"O nome de usuário é usado para autenticaçào e é como outros usuário irão vê-lo em suas publicações e comentários. Ele deve ter pelo menos 3 caracteres e não ultrapassar 16 caracteres, contendo apenas letras minúsculas e números."'::hstore );
SELECT insert_sentences_page( 'LINK_ACCOUNT', 'LINK_ACCOUNT', '"1033"=>"Link Account", "1046"=>"Vincular Conta"'::hstore );
SELECT insert_sentences_page( 'TYPE_HERE', 'LINK_ACCOUNT', '"1033"=>"Type Here", "1046"=>"Digite Aqui"'::hstore );
SELECT insert_sentences_page( 'VALID_USERNAME', 'LINK_ACCOUNT', '"1033"=>"The username must be at least 3 characters long and do not exceed 16 characters, containing only lowercase letters and numbers.", "1046"=>"O nome de usuário deve ter pelo menos 3 caracteres e não ultrapassar 16 caracteres, contendo apenas letras minúsculas e números."'::hstore );
SELECT insert_sentences_page( 'VALID_PASSWORD', 'LINK_ACCOUNT', '"1033"=>"The password must be at least 8 characters long, containing at least one number, one special character, one uppercase letter and one lowercase letter. I know you can do it!", "1046"=>"A senha deve ter pelo menos 8 caracteres, contendo pelo menos um número, um caracter especial, uma letra maiúscula e uma letra minúscula. Eu sei que você consegue!"'::hstore );
SELECT insert_sentences_page( 'USERNAME', 'LINK_ACCOUNT', '"1033"=>"Username", "1046"=>"Nome de Usuário"'::hstore );
SELECT insert_sentences_page( 'EMAIL', 'LINK_ACCOUNT', '"1033"=>"email", "1046"=>"e-mail"'::hstore );
SELECT insert_sentences_page( 'FULLNAME', 'LINK_ACCOUNT', '"1033"=>"Fullname", "1046"=>"Nome Completo"'::hstore );
SELECT insert_sentences_page( 'FIRSTNAME', 'LINK_ACCOUNT', '"1033"=>"First Name", "1046"=>"Primeiro Nome"'::hstore );
SELECT insert_sentences_page( 'LASTNAME', 'LINK_ACCOUNT', '"1033"=>"Last Name", "1046"=>"Sobrenome"'::hstore );
SELECT insert_sentences_page( 'LINK', 'LINK_ACCOUNT', '"1033"=>"Link", "1046"=>"Vincular"'::hstore );
SELECT insert_sentences_page( 'CREATE_ACCOUNT', 'LINK_ACCOUNT', '"1033"=>"Create Account", "1046"=>"Criar Conta"'::hstore );
SELECT insert_sentences_page( 'CLEAR', 'LINK_ACCOUNT', '"1033"=>"Clear", "1046"=>"Limpar"'::hstore );
SELECT insert_sentences_page( 'LOGIN_ACCOUNT', 'LINK_ACCOUNT', '"1033"=>"Username/e-mail", "1046"=>"Usuário/e-mail"'::hstore );
SELECT insert_sentences_page( 'PASSWORD', 'LINK_ACCOUNT', '"1033"=>"Password", "1046"=>"Senha"'::hstore );
SELECT insert_sentences_page( 'CONFIRM_PASSWORD', 'LINK_ACCOUNT', '"1033"=>"Confirm Password", "1046"=>"Confirmar Senha"'::hstore );
SELECT insert_sentences_page( 'BACK_TO_PREVIOUS_QUESTION', 'LINK_ACCOUNT', '"1033"=>"Go back to previous question", "1046"=>"Voltar à pergunta anterior"'::hstore );
SELECT insert_sentences_page( 'FIRST_ACCESS', 'LINK_ACCOUNT', '"1033"=>"This is my first access", "1046"=>"Este é meu primeiro acesso"'::hstore );
SELECT insert_sentences_page( 'HAVE_ACCOUNT', 'LINK_ACCOUNT', '"1033"=>"I have an account", "1046"=>"Eu já possuo uma conta"'::hstore );
SELECT insert_sentences_page( 'DO_YOU_HAVE_AN_ACCOUNT', 'LINK_ACCOUNT', '"1033"=>"Your login information was validated for \"[[-USER-]]@[[-DOMAIN-]]\" but it is not linked to any existent account in this system. Do you already have an account or is this your first access?", "1046"=>"As informações de login \"[[-USER-]]@[[-DOMAIN-]]\" foram validadas mas não está vinculada a nenhuma conta existente no sistema. Você já possui uma conta ou este é seu primeiro acesso?"'::hstore );
SELECT insert_sentences_page( 'YOU_HAVE_ACCOUNT_WHICH_ACCOUNT', 'LINK_ACCOUNT', '"1033"=>"You have told us that you already have an account in the system, is that right? If so please provide its information below so we can link it to \"[[-USER-]]@[[-DOMAIN-]]\" account. If you are here by mistake just go back to previous question and select another option.", "1046"=>"Você nos disse que já possui uma conta no sistema, correto? Então por favor indique suas credenciais nos campos abaixo para que possamos vinculá-la à sua conta \"[[-USER-]]@[[-DOMAIN-]]\". Se estiver aqui por engano apenas clique em voltar à pergunta anterior e selecione outra opção."'::hstore );
SELECT insert_sentences_page( 'FIRST_ACCESS_CREATE_ACCOUNT', 'LINK_ACCOUNT', '"1033"=>"You have told us that this is your first access. Don''t worry, you just need to fill the information below to create an account in the system and we will automatically link \"[[-USER-]]@[[-DOMAIN-]]\" to it. If you need further instructions or specific permissions just contact us and explain your needs. if you are here by mistake just go back to the previous question and select another option.", "1046"=>"Você nos disse que este é seu primeiro acesso ao sistema. Não se preocupe, você precisa apenas preencher as informações abaixo para criar sua conta e iremos vincular \"[[-USER-]]@[[-DOMAIN-]]\" automaticamente a ela. Se precisar de instruções ou permissões específicas apenas nos contate e explique suas necessidades. Se estiver aqui por engano apenas clique em voltar à pergunta anterior e selecione outra opção."'::hstore );


SELECT insert_sentences_page( 'VALID_USERNAME', 'CHANGE_USERNAME_MESSAGES', '"1033"=>"The username must be at least 3 characters long and do not exceed 16 characters, containing only lowercase letters and numbers.", "1046"=>"O nome de usuário deve ter pelo menos 3 caracteres e não ultrapassar 16 caracteres, contendo apenas letras minúsculas e números."'::hstore );
SELECT insert_sentences_page( 'TAKEN_USERNAME', 'CHANGE_USERNAME_MESSAGES', '"1033"=>"The username you choosed is already taken... please try another", "1046"=>"O nome de usuário que você escolheu já está em uso... tente novamente"'::hstore );
SELECT insert_sentences_page( 'USERNAME_UNCHANGED', 'CHANGE_USERNAME_MESSAGES', '"1033"=>"The username has not been changed", "1046"=>"O nome de usuário não foi alterado"'::hstore );
SELECT insert_sentences_page( 'USERNAME_CHANGED', 'CHANGE_USERNAME_MESSAGES', '"1033"=>"Your username has been updated", "1046"=>"O seu nome de usuário foi atualizado"'::hstore );
SELECT insert_sentences_page( 'USERNAME_CHANGED_TOO_RECENTLY', 'CHANGE_USERNAME_MESSAGES', '"1033"=>"Your username has been changed too recently to change it again", "1046"=>"O seu nome de usuário foi alterado muito recentemente para fazer uma nova alteração"'::hstore );

SELECT insert_sentences_page( 'VALID_USERNAME', 'CREATE_ACCOUNT_ERRORS', '"1033"=>"The username must be at least 3 characters long and do not exceed 16 characters, containing only lowercase letters and numbers.", "1046"=>"O nome de usuário deve ter pelo menos 3 caracteres e não ultrapassar 16 caracteres, contendo apenas letras minúsculas e números."'::hstore );
SELECT insert_sentences_page( 'TAKEN_USERNAME', 'CREATE_ACCOUNT_ERRORS', '"1033"=>"The username you choosed is already taken... please try another", "1046"=>"O nome de usuário que você escolheu já está em uso... tente novamente"'::hstore );
SELECT insert_sentences_page( 'ERROR_BLANK_FIRSTNAME', 'CREATE_ACCOUNT_ERRORS', '"1033"=>"first name should not be blank", "1046"=>"primeiro nome não deve estar em branco"'::hstore );
SELECT insert_sentences_page( 'ERROR_VALID_FIRSTNAME', 'CREATE_ACCOUNT_ERRORS', '"1033"=>"first name is invalid", "1046"=>"primeiro nome é inválido"'::hstore );
SELECT insert_sentences_page( 'ERROR_BLANK_LASTNAME', 'CREATE_ACCOUNT_ERRORS', '"1033"=>"last name should not be blank", "1046"=>"sobrenome não deve estar em branco"'::hstore );
SELECT insert_sentences_page( 'ERROR_VALID_LASTNAME', 'CREATE_ACCOUNT_ERRORS', '"1033"=>"last name is invalid", "1046"=>"sobrenome \e inválido"'::hstore );
SELECT insert_sentences_page( 'ERROR_BLANK_FULLNAME', 'CREATE_ACCOUNT_ERRORS', '"1033"=>"fullname should not be blank", "1046"=>"nome completo não deve estar em branco"'::hstore );
SELECT insert_sentences_page( 'ERROR_INVALID_FULLNAME', 'CREATE_ACCOUNT_ERRORS', '"1033"=>"fullname is invalid", "1046"=>"nome completo é inválido"'::hstore );
SELECT insert_sentences_page( 'ERROR_BLANK_EMAIL', 'CREATE_ACCOUNT_ERRORS', '"1033"=>"email should not be blank", "1046"=>"e-mail não deve estar em branco"'::hstore );
SELECT insert_sentences_page( 'ERROR_INVALID_EMAIL', 'CREATE_ACCOUNT_ERRORS', '"1033"=>"The e-mail you provided is invalid", "1046"=>"O e-mail informado é inválido"'::hstore );
SELECT insert_sentences_page( 'ERROR_TAKEN_EMAIL', 'CREATE_ACCOUNT_ERRORS', '"1033"=>"The e-mail you provided is already registered to an account", "1046"=>"O e-mail informado já está registrado em uma conta"'::hstore );
SELECT insert_sentences_page( 'VALID_PASSWORD', 'CREATE_ACCOUNT_ERRORS', '"1033"=>"The password must be at least 8 characters long, containing at least one number, one special character, one uppercase letter and one lowercase letter. I know you can do it!", "1046"=>"A senha deve ter pelo menos 8 caracteres, contendo pelo menos um número, um caracter especial, uma letra maiúscula e uma letra minúscula. Eu sei que você consegue!"'::hstore );
SELECT insert_sentences_page( 'ERROR_BLANK_BIRTHDAY', 'CREATE_ACCOUNT_ERRORS', '"1033"=>"birthday should not be blank", "1046"=>"data de nascimento não deve estar em branco"'::hstore );
SELECT insert_sentences_page( 'ERROR_AT_LEAST_18_YEARS', 'CREATE_ACCOUNT_ERRORS', '"1033"=>"you should be at least 18 years old", "1046"=>"você ter ter pelo menos 18 anos de idade."'::hstore );
SELECT insert_sentences_page( 'ERROR_MAX_100_YEARS', 'CREATE_ACCOUNT_ERRORS', '"1033"=>"you should be at most 100 years old", "1046"=>"você ter ter no máximo 100 anos de idade."'::hstore );
SELECT insert_sentences_page( 'PASSWORD_AND_CONFIRM_MUST_MATCH', 'CREATE_ACCOUNT_ERRORS', '"1033"=>"The password and its confirmation must match", "1046"=>"A senha e sua confirmação devem ser idênticas"'::hstore );

SELECT insert_sentences_page( 'VALID_PASSWORD', 'CHANGE_PASSWORD_MESSAGES', '"1033"=>"The password must be at least 8 characters long, containing at least one number, one special character, one uppercase letter and one lowercase letter. I know you can do it!", "1046"=>"A senha deve ter pelo menos 8 caracteres, contendo pelo menos um número, um caracter especial, uma letra maiúscula e uma letra minúscula. Eu sei que você consegue!"'::hstore );
SELECT insert_sentences_page( 'NEW_AND_CONFIRM_PASSWORD_MUST_MATCH', 'CHANGE_PASSWORD_MESSAGES', '"1033"=>"The new password and its confirmation must match", "1046"=>"A nova senha e sua confirmação devem ser idênticas"'::hstore );
SELECT insert_sentences_page( 'PASSWORD_CHANGED', 'CHANGE_PASSWORD_MESSAGES', '"1033"=>"The password has been changed", "1046"=>"A senha foi alterada"'::hstore );
SELECT insert_sentences_page( 'INCORRECT_CURRENT_PASSWORD', 'CHANGE_PASSWORD_MESSAGES', '"1033"=>"The current password is incorrect", "1046"=>"A senha atual está incorreta"'::hstore );
SELECT insert_sentences_page( 'NEW_PASSWORD_MUST_BE_DIFFERENT', 'CHANGE_PASSWORD_MESSAGES', '"1033"=>"New password must not be the same as the current password", "1046"=>"A nova senha deve ser diferente da senha atual"'::hstore );
SELECT insert_sentences_page( 'VALID_PASSWORD', 'CHANGE_PASSWORD', '"1033"=>"The password must be at least 8 characters long, containing at least one number, one special character, one uppercase letter and one lowercase letter. I know you can do it!", "1046"=>"A senha deve ter pelo menos 8 caracteres, contendo pelo menos um número, um caracter especial, uma letra maiúscula e uma letra minúscula. Eu sei que você consegue!"'::hstore );
SELECT insert_sentences_page( 'INVALID_CURRENT_PASSWORD', 'CHANGE_PASSWORD_MESSAGES', '"1033"=>"The current password for your account is invalid", "1046"=>"Não conseguimos validar sua senha atual"'::hstore );
SELECT insert_sentences_page( 'PROVIDE_YOUR_CURRENT_PASSWORD', 'CHANGE_PASSWORD_MESSAGES', '"1033"=>"You should provide your current password", "1046"=>"Voc6e deve informar sua senha atual"'::hstore );
SELECT insert_sentences_page( 'SAME_PASSWORD', 'CHANGE_PASSWORD_MESSAGES', '"1033"=>"The new password must be different from current password", "1046"=>"A senha nova deve ser diferente da senha atual"'::hstore );
SELECT insert_sentences_page( 'COULD_NOT_SET_PASSWORD_FOR', 'CHANGE_PASSWORD_MESSAGES', '"1033"=>"Could not set password on server", "1046"=>"Não foi possível definir a senha no servidor"'::hstore );


SELECT insert_sentences_page( 'PREPARING', 'MESSAGES', '"1033"=>"Preparing", "1046"=>"Preparando"'::hstore );
SELECT insert_sentences_page( 'SENDING', 'MESSAGES', '"1033"=>"Sending", "1046"=>"Enviando"'::hstore );
SELECT insert_sentences_page( 'SAVE', 'MESSAGES', '"1033"=>"Save", "1046"=>"Salvar"'::hstore );
SELECT insert_sentences_page( 'CANCEL', 'MESSAGES', '"1033"=>"Cancel", "1046"=>"Cancelar"'::hstore );
SELECT insert_sentences_page( 'PROCESSING', 'MESSAGES', '"1033"=>"Processing", "1046"=>"Processando"'::hstore );
SELECT insert_sentences_page( 'SOMETHING_HAS_GONE_BAD', 'MESSAGES', '"1033"=>"Something has gone bad...", "1046"=>"Algo deu errado..."'::hstore );

SELECT insert_sentences_page( 'PARAGRAPH', 'MESSAGES_NEW_POST', '"1033"=>"Parapragh", "1046"=>"Parágrafo"'::hstore );

SELECT insert_sentences_page( 'PHONE', 'CONTACT', '"1033"=>"phone", "1046"=>"telefone"'::hstore );
SELECT insert_sentences_page( 'EMAIL', 'CONTACT', '"1033"=>"email", "1046"=>"e-mail"'::hstore );
SELECT insert_sentences_page( 'CONTACT', 'CONTACT', '"1033"=>"Contact", "1046"=>"Contato"'::hstore );
SELECT insert_sentences_page( 'NAME', 'CONTACT', '"1033"=>"Name", "1046"=>"Nome"'::hstore );
SELECT insert_sentences_page( 'SUBJECT', 'CONTACT', '"1033"=>"Subject", "1046"=>"Assunto"'::hstore );
SELECT insert_sentences_page( 'MESSAGE', 'CONTACT', '"1033"=>"Message", "1046"=>"Mensagem"'::hstore );
SELECT insert_sentences_page( 'CLEAR', 'CONTACT', '"1033"=>"Clear", "1046"=>"Limpar"'::hstore );
SELECT insert_sentences_page( 'SEND', 'CONTACT', '"1033"=>"Send", "1046"=>"Enviar"'::hstore );
SELECT insert_sentences_page( 'FORM', 'CONTACT', '"1033"=>"Form", "1046"=>"Formulário"'::hstore );
SELECT insert_sentences_page( 'CONTACT_MESSAGE', 'CONTACT', '"1033"=>"This page is for internal use only, if you understand that it is being rendered unduly please inform us and try to be as more specific as possible.", "1046"=>"Esta página é somente para uso interno, caso você entenda que ela esteja sendo renderizada indevidamente por favor nos informe e tente ser o mais específico possível."'::hstore );

SELECT insert_sentences_page( 'YOUR_ACCOUNT', 'SEND_ACCOUNT', '"1033"=>"your [[-TITLE-]] account", "1046"=>"sua conta [[-TITLE-]]"'::hstore );
SELECT insert_sentences_page( 'YOUR_ACCOUNT_BODY', 'SEND_ACCOUNT', '"1033"=>"Dear User,<br /><br />Welcome to [[-URL-]]!<br /><br />Your account has been succesfully created with the following information:<br />Username: [[-USERNAME-]]<br />First Name: [[-FIRSTNAME-]]<br />Last Name: [[-LASTNAME-]]<br /><br />Feel free to contact us at any time.", "1046"=>"Prezado Usuário,<br /><br />Seja bem-vindo ao [[-URL-]]!<br /><br />Sua conta foi criada com sucesso com as seguintes informações:<br />Nome de Usuário: [[-USERNAME-]]<br />Primeiro Nome: [[-FIRSTNAME-]]<br />Sobrenome: [[-LASTNAME-]]<br /><br />Sinta-se à vontade para entrar em contato conosco a qualquer momento."'::hstore );

SELECT insert_sentences_page( 'YOUR_PASSWORD', 'SEND_PASSWORD', '"1033"=>"password recovery for [[-TITLE-]]", "1046"=>"recuperação de senha para [[-TITLE-]]"'::hstore );
SELECT insert_sentences_page( 'SENT_TO_EMAIL', 'SEND_PASSWORD', '"1033"=>"an email was sent to [[-EMAIL-]] to continue the redefinition of your password. The link is valid for 30 minutes and another one will not be generated before this one expires", "1046"=>"um e-mail foi enviado para [[-EMAIL-]] contendo o link para continuar a redefinição da senha. O link é válido por 30 minutos e outro não será gerado antes que este expire"'::hstore );
SELECT insert_sentences_page( 'YOUR_PASSWORD_BODY', 'SEND_PASSWORD', '"1033"=>"Dear User,<br /><br />It was requested to reset your password for [[-URL-]]!<br /><br />Please access the link below ro continue the procedure:<br />[[-LINK-]]<br />The requisition was done from [[-ADDRESS-]].<br /><br />Feel free to contact us at any time.", "1046"=>"Prezado Usuário,<br /><br />Foi requisitada a redefinição da sua senha em [[-URL-]]!<br /><br />Por gentileza, siga o link abaixo para continuar o procedimento:<br />[[-LINK-]]<br />A requisição foi feita a partir do endereço: [[-ADDRESS-]]!<br /><br />Sinta-se à vontade para entrar em contato conosco a qualquer momento."'::hstore );

SELECT insert_sentences_page( 'YOURS_TRULY', 'SIGNATURE', '"1033"=>"Yours truly", "1046"=>"Sinceramente"'::hstore );
SELECT insert_sentences_page( 'DEVELOPER', 'SIGNATURE', '"1033"=>"Developer", "1046"=>"Desenvolvedor"'::hstore );

--# MY ACCOUNT PAGE
SELECT insert_sentences_page( 'PASSWORD', 'MY_ACCOUNT', '"1033"=>"Password", "1046"=>"Senha"'::hstore );
SELECT insert_sentences_page( 'CHANGE_PASSWORD', 'MY_ACCOUNT', '"1033"=>"Change Password", "1046"=>"Alterar Senha"'::hstore );
SELECT insert_sentences_page( 'ACCOUNT', 'MY_ACCOUNT', '"1033"=>"Account", "1046"=>"Conta"'::hstore );
SELECT insert_sentences_page( 'USERNAME', 'MY_ACCOUNT', '"1033"=>"Username", "1046"=>"Nome du usuário"'::hstore );
SELECT insert_sentences_page( 'ITEMS_PER_PAGE', 'MY_ACCOUNT', '"1033"=>"Items per page", "1046"=>"Items por página"'::hstore );
SELECT insert_sentences_page( 'HIDE_ON_BLUR', 'MY_ACCOUNT', '"1033"=>"Hide when loses focus", "1046"=>"Esconder quando perder o foco"'::hstore );
SELECT insert_sentences_page( 'PANIC_ACTION', 'MY_ACCOUNT', '"1033"=>"Hide on double click", "1046"=>"Esconder quando usar clique duplo"'::hstore );
SELECT insert_sentences_page( 'MY_ACCOUNT', 'MY_ACCOUNT', '"1033"=>"My Account", "1046"=>"Minha Conta"'::hstore );
SELECT insert_sentences_page( 'USER_NOT_FOUND', 'MY_ACCOUNT', '"1033"=>"An account with the provided information was not found", "1046"=>"Uma conta com as informações providas não foi encontrada"'::hstore );
SELECT insert_sentences_page( 'VALID_USERNAME', 'MY_ACCOUNT', '"1033"=>"The username must be at least 3 characters long and do not exceed 16 characters, containing only lowercase letters and numbers.", "1046"=>"O nome de usuário deve ter pelo menos 3 caracteres e não ultrapassar 16 caracteres, contendo apenas letras minúsculas e números."'::hstore );
SELECT insert_sentences_page( 'INVALID_USERNAME', 'MY_ACCOUNT', '"1033"=>"The username is invalid, it must be at least 3 characters long and do not exceed 16 characters, containing only lowercase letters and numbers.", "1046"=>"O nome de usuário é inválido, ele deve ter pelo menos 3 caracteres e não ultrapassar 16 caracteres, contendo apenas letras minúsculas e números."'::hstore );

--# LOGIN PAGE
SELECT insert_sentences_page( 'LOGIN_HELP', 'LOGIN', '"1033"=>"You may use your system account username or email as also as external domain accounts, if available. For external domain accounts please use the notation \"DOMAIN\\USER\" or \"USER@DOMAIN\".", "1046"=>"Você pode usar seu nome de usuário ou e-mail registrados no sistema, ou também contas de domínios externos. Para contas de domínios externos por favor use a notação \"DOMINIO\\USUARIO\" ou \"USUARIO@DOMINIO\"."'::hstore );
SELECT insert_sentences_page( 'EXTERNAL_DOMAINS', 'LOGIN', '"1033"=>"The available domains for external validation are", "1046"=>"Os domínios externos disponíveis são"'::hstore );
SELECT insert_sentences_page( 'NO_EXTERNAL_DOMAINS', 'LOGIN', '"1033"=>"There are no external domain available", "1046"=>"Não há nenhum domínio externo disponível"'::hstore );
SELECT insert_sentences_page( 'TYPE_HERE', 'LOGIN', '"1033"=>"type here", "1046"=>"digite aqui"'::hstore );
SELECT insert_sentences_page( 'LOGIN_HELP', 'LOGIN', '"1033"=>"type here", "1046"=>"digite aqui"'::hstore );
SELECT insert_sentences_page( 'SIGNIN', 'LOGIN', '"1033"=>"Sign In", "1046"=>"Login"'::hstore );
SELECT insert_sentences_page( 'SIGNUP', 'LOGIN', '"1033"=>"Sign Up", "1046"=>"Registrar"'::hstore );
SELECT insert_sentences_page( 'CLEAR', 'LOGIN', '"1033"=>"Clear", "1046"=>"Limpar"'::hstore );
SELECT insert_sentences_page( 'LOGIN', 'LOGIN', '"1033"=>"Login", "1046"=>"Autenticar"'::hstore );
SELECT insert_sentences_page( 'CREATE_ACCOUNT', 'LOGIN', '"1033"=>"Create Account", "1046"=>"Criar Conta"'::hstore );
SELECT insert_sentences_page( 'USERNAME', 'LOGIN', '"1033"=>"Username", "1046"=>"Nome de Usuário"'::hstore );
SELECT insert_sentences_page( 'EMAIL', 'LOGIN', '"1033"=>"Email", "1046"=>"E-mail"'::hstore );
SELECT insert_sentences_page( 'PASSWORD', 'LOGIN', '"1033"=>"Password", "1046"=>"Senha"'::hstore );
SELECT insert_sentences_page( 'PERSONAL_COMPUTER', 'LOGIN', '"1033"=>"Personal Computer", "1046"=>"Computador Pessoal"'::hstore );
SELECT insert_sentences_page( 'KEEP_CONNECTED', 'LOGIN', '"1033"=>"Keep Connected", "1046"=>"Manter Conectado"'::hstore );
SELECT insert_sentences_page( 'KEEP_CONNECTED_HELP', 'LOGIN', '"1033"=>"Enable this flag if you want to preserve your login in after closing the browser. This is only recommended if you are the only user of the computer you are using at this moment..", "1046"=>"Ative se quiser preservar seu login após fechar o navegador. isto é recomendado apenas se você for o único usuário do computador que está usando neste momento."'::hstore );
SELECT insert_sentences_page( 'FORGOT_PASSWORD', 'LOGIN', '"1033"=>"Forgot your password? Click here", "1046"=>"Esqueceu sua senha? Clique aqui"'::hstore );
SELECT insert_sentences_page( 'LOGIN_ACCOUNT', 'LOGIN', '"1033"=>"Username/e-mail", "1046"=>"Usuário/e-mail"'::hstore );
SELECT insert_sentences_page( 'PAGE_SIGNIN_LINE_1', 'LOGIN', '"1033"=>"Provide your access information to identify yourself in our system", "1046"=>"Preencha suas informações de acesso para se identificar em nosso sistema"'::hstore );
SELECT insert_sentences_page( 'PAGE_SIGNUP_LINE_1', 'LOGIN', '"1033"=>"Fill the form below to register yourself, the temporary password will be sent to the provided e-mail", "1046"=>"Complete o formulário abaixo para se registrar, a senha temporária será enviada ao e-mail informado."'::hstore );
SELECT insert_sentences_page( 'PAGE_SIGNUP_NO_NEW_SIGNUPS', 'LOGIN', '"1033"=>"The creation of new accounts are currently blocked, please provide your e-mail and the username you wish and you will be notified as soon as new accounts are available", "1046"=>"A criança de novas contas está temporariamente bloqueada, por favor informe seu e-mail e o nome de usuário que deseja reservar e assim que a criação estiver liberada entraremos em contato."'::hstore );

--# RECOVER PASSWORD PAGE
SELECT insert_sentences_page( 'RECOVER_PASSWORD_MESSAGE_1', 'RECOVER_PASSWORD', '"1033"=>"You forgot your password, it happens! We will help you generate a new one.", "1046"=>"Você esqueceu sua senha, acontece! Vamos ajudá-lo a criar uma nova."'::hstore );
SELECT insert_sentences_page( 'RECOVER_PASSWORD_MESSAGE_2', 'RECOVER_PASSWORD', '"1033"=>"First, we need some information of you, like your username if you remember it, or your e-mail. Once we have identified you in the system we will send you e-mail with a link to continue the procedure. ", "1046"=>"Inicialmente vamos precisar de alguma informação sua, como seu nome de usuário, caso você se lembre dele, ou do seu e-mail. Assim que identificarmos você no sistema enviaremos um e-mail com um link para continuar o procedimento."'::hstore );
SELECT insert_sentences_page( 'RECOVER_PASSWORD_MESSAGE_3', 'RECOVER_PASSWORD', '"1033"=>"Outch! We made it! This is the last step to define a new password for your account, you just need to fill the new password below according to the complexity criteria. The confirmation field is important to avoid mistyping... we hope that you don''t make the same mistake twice! ", "1046"=>"Ufa! Chegamos no último passo para redefinir sua senha! Basta preenchê-la abaixo seguindo os critérios de complexidade. O campo de confirmação é fundamental para evitar erros de digitação... esperamos que não erre duas vezes do mesmo jeito!"'::hstore );
SELECT insert_sentences_page( 'VALID_PASSWORD', 'RECOVER_PASSWORD', '"1033"=>"The password must be at least 8 characters long, containing at least one number, one special character, one uppercase letter and one lowercase letter. I know you can do it!", "1046"=>"A senha deve ter pelo menos 8 caracteres, contendo pelo menos um número, um caracter especial, uma letra maiúscula e uma letra minúscula. Eu sei que você consegue!"'::hstore );
SELECT insert_sentences_page( 'LOGIN_ACCOUNT', 'RECOVER_PASSWORD', '"1033"=>"Username/e-mail", "1046"=>"Usuário/e-mail"'::hstore );
SELECT insert_sentences_page( 'SET_PASSWORD', 'RECOVER_PASSWORD', '"1033"=>"Set Password", "1046"=>"definir senha"'::hstore );
SELECT insert_sentences_page( 'NEW_PASSWORD', 'RECOVER_PASSWORD', '"1033"=>"New Password", "1046"=>"nova senha"'::hstore );
SELECT insert_sentences_page( 'CONFIRM_PASSWORD', 'RECOVER_PASSWORD', '"1033"=>"Confirm Password", "1046"=>"confirmar senha"'::hstore );
SELECT insert_sentences_page( 'FIRST_NAME', 'RECOVER_PASSWORD', '"1033"=>"First Name", "1046"=>"Primeiro Nome"'::hstore );
SELECT insert_sentences_page( 'LAST_NAME', 'RECOVER_PASSWORD', '"1033"=>"Last Name", "1046"=>"Sobrenome"'::hstore );
SELECT insert_sentences_page( 'TYPE_HERE', 'RECOVER_PASSWORD', '"1033"=>"type here", "1046"=>"digite aqui"'::hstore );
SELECT insert_sentences_page( 'CLEAR', 'RECOVER_PASSWORD', '"1033"=>"Clear", "1046"=>"Limpar"'::hstore );
SELECT insert_sentences_page( 'RECOVER_PASSWORD', 'RECOVER_PASSWORD', '"1033"=>"Recover Password", "1046"=>"Recuperar Senha"'::hstore );
SELECT insert_sentences_page( 'PASSWORD_RECOVERY', 'RECOVER_PASSWORD', '"1033"=>"Password Recovery", "1046"=>"Recuperação de Senha"'::hstore );
SELECT insert_sentences_page( 'GO_BACK', 'RECOVER_PASSWORD', '"1033"=>"go back", "1046"=>"voltar"'::hstore );
SELECT insert_sentences_page( 'NETWORK_BLOCKED', 'RECOVER_PASSWORD', '"1033"=>"The network you are accessing from was blocked for security reasons", "1046"=>"A rede de onde você está acessando foi bloqueada por razões de segurança"'::hstore );
SELECT insert_sentences_page( 'TOKEN_NOT_FOUND', 'RECOVER_PASSWORD', '"1033"=>"this link is not valid. This may happen if your link has expired or if your ip address is different from the one in which the requisition was made.", "1046"=>"Este link não é válido. isto pode ocorrer se seu link expirou ou está acessando de um endereço IP diferente do que a requisição foi realizada."'::hstore );


SELECT insert_sentences_page( 'TOKEN_NOT_FOUND', 'PASSWORD_RECOVERY_MESSAGES', '"1033"=>"this link is not valid. This may happen if your link has expired or if your ip address is different from the one in which the requisition was made.", "1046"=>"Este link não é válido. isto pode ocorrer se seu link expirou ou está acessando de um endereço IP diferente do que a requisição foi realizada."'::hstore );
SELECT insert_sentences_page( 'VALID_PASSWORD', 'PASSWORD_RECOVERY_MESSAGES', '"1033"=>"The password must be at least 8 characters long, containing at least one number, one special character, one uppercase letter and one lowercase letter. I know you can do it!", "1046"=>"A senha deve ter pelo menos 8 caracteres, contendo pelo menos um número, um caracter especial, uma letra maiúscula e uma letra minúscula. Eu sei que você consegue!"'::hstore );
SELECT insert_sentences_page( 'ERROR_BLANK_LOGIN', 'PASSWORD_RECOVERY_MESSAGES', '"1033"=>"you must provide your username or email", "1046"=>"você deve informar seu nome de usuário ou e-mail"'::hstore );
SELECT insert_sentences_page( 'ACCOUNT_NOT_FOUND', 'PASSWORD_RECOVERY_MESSAGES', '"1033"=>"We were unable to find an account with the information you have provided.", "1046"=>"Não conseguimos encontrar uma conta com as informações providas"'::hstore );
SELECT insert_sentences_page( 'NETWORK_BLOCKED', 'PASSWORD_RECOVERY_MESSAGES', '"1033"=>"The network you are accessing from was blocked for security reasons to any user interaction", "1046"=>"A rede pelo qual você está acessando foi bloqueada por razões de segurança para qualquer interação de usuário"'::hstore );
SELECT insert_sentences_page( 'RECOVER_PASSWORD_EMAIL_SUBJECT', 'PASSWORD_RECOVERY_MESSAGES', '"1033"=>"password recovery", "1046"=>"recuperação de senha"'::hstore );
SELECT insert_sentences_page( 'RECOVERY_IN_PROGRESS', 'PASSWORD_RECOVERY_MESSAGES', '"1033"=>"There is an active requisition for this account, please wait until it expires before requesting again", "1046"=>"Já tem uma requisição ativa para esta conta, por favor aguarde até que ela expire antes de realizar uma nova requisição."'::hstore );
SELECT insert_sentences_page( 'NEW_AND_CONFIRM_PASSWORD_MUST_MATCH', 'PASSWORD_RECOVERY_MESSAGES', '"1033"=>"The new password and its confirmation must match", "1046"=>"A nova senha e sua confirmação devem ser idênticas"'::hstore );
SELECT insert_sentences_page( 'PASSWORD_CHANGED', 'PASSWORD_RECOVERY_MESSAGES', '"1033"=>"The has been changed", "1046"=>"A senha foi alterada"'::hstore );


--# PAGE SENTENCES
SELECT insert_sentences_page( 'FIRST_PAGE', 'PAGE_COUNTER', '"1033"=>"First", "1046"=>"Primeira"'::hstore );
SELECT insert_sentences_page( 'PREVIOUS_PAGE', 'PAGE_COUNTER', '"1033"=>"Previous", "1046"=>"Anterior"'::hstore );
SELECT insert_sentences_page( 'PAGE', 'PAGE_COUNTER', '"1033"=>"Page", "1046"=>"Página"'::hstore );
SELECT insert_sentences_page( 'NEXT_PAGE', 'PAGE_COUNTER', '"1033"=>"Next", "1046"=>"Próxima"'::hstore );
SELECT insert_sentences_page( 'LAST_PAGE', 'PAGE_COUNTER', '"1033"=>"Last", "1046"=>"Última"'::hstore );

--# MANAGE USERS
SELECT insert_sentences_page( 'USERS', 'MANAGE_USERS', '"1033"=>"Users", "1046"=>"Usuários"'::hstore );
SELECT insert_sentences_page( 'FILTER', 'MANAGE_USERS', '"1033"=>"Filter", "1046"=>"Filtrar"'::hstore );
SELECT insert_sentences_page( 'SEARCH', 'MANAGE_USERS', '"1033"=>"Search", "1046"=>"Procurar"'::hstore );
SELECT insert_sentences_page( 'TYPE_HERE', 'MANAGE_USERS', '"1033"=>"Type here", "1046"=>"Digite aqui"'::hstore );
SELECT insert_sentences_page( 'ADD_USER', 'MANAGE_USERS', '"1033"=>"add user", "1046"=>"adicionar usuário"'::hstore );
SELECT insert_sentences_page( 'ADD_USER_HELP', 'MANAGE_USERS', '"1033"=>"add a new local user in the system", "1046"=>"adicione um novo usuário local ao sistema"'::hstore );
SELECT insert_sentences_page ( 'CLICK_TO_EXPAND_OR_COLLAPSE', 'MANAGE_USERS', '"1033"=>"Click here to expand or collapse content", "1046"=>"Clique aqui para expandir ou comprimir o conteúdo"'::hstore );


--# FILTER SENTENCES
SELECT insert_sentences_page( 'SEARCH', 'FILTER', '"1033"=>"Search", "1046"=>"Procurar"'::hstore );
SELECT insert_sentences_page( 'CLEAR', 'FILTER', '"1033"=>"Clear", "1046"=>"Limpar"'::hstore );

--# SITE INFORMATION PAGE
SELECT insert_sentences_page( 'SITE_INFORMATION', 'SITE_INFORMATION', '"1033"=>"Site Information", "1046"=>"Informações do Site"'::hstore );
SELECT insert_sentences_page( 'SITE_INFORMATION_MESSAGE', 'SITE_INFORMATION', '"1033"=>"Here you can edit information of the site", "1046"=>"Você pode editar as informações do site"'::hstore );

--# SITE CONFIGURATION PAGE
SELECT insert_sentences_page( 'SITE_CONFIGURATION', 'SITE_CONFIGURATION', '"1033"=>"Site Configuration", "1046"=>"Configurações do Site"'::hstore );

--# CHANGE PASSWORD PAGE
SELECT insert_sentences_page( 'NETWORK_BLOCKED', 'CHANGE_PASSWORD', '"1033"=>"The network you are accessing from was blocked for security reasons", "1046"=>"A rede de onde você está acessando foi bloqueada por razões de segurança"'::hstore );
SELECT insert_sentences_page( 'TYPE_HERE', 'CHANGE_PASSWORD', '"1033"=>"Type Here", "1046"=>"Digite Aqui"'::hstore );
SELECT insert_sentences_page( 'CHANGE_PASSWORD', 'CHANGE_PASSWORD', '"1033"=>"Change Password", "1046"=>"Alterar Senha"'::hstore );
SELECT insert_sentences_page( 'PAGE_CHANGE_PASSWORD_LINE_1', 'CHANGE_PASSWORD', '"1033"=>"The new password must contain at least 8 characters and contains uppercase and lowercase letters and numbers", "1046"=>"A nova senha deve ter pelo menos 8 caracteres e conter letras maiúsculas e minúsculas e números"'::hstore );
SELECT insert_sentences_page( 'CURRENT_PASSWORD', 'CHANGE_PASSWORD', '"1033"=>"Current Password", "1046"=>"Senha Atual"'::hstore );
SELECT insert_sentences_page( 'CURRENT_PASSWORD_FOR', 'CHANGE_PASSWORD', '"1033"=>"Current Password for", "1046"=>"Senha Atual de"'::hstore );
SELECT insert_sentences_page( 'NEW_PASSWORD', 'CHANGE_PASSWORD', '"1033"=>"New password", "1046"=>"Nova Senha"'::hstore );
SELECT insert_sentences_page( 'NEW_PASSWORD_FOR', 'CHANGE_PASSWORD', '"1033"=>"New password for", "1046"=>"Nova Senha para"'::hstore );
SELECT insert_sentences_page( 'GO_BACK', 'CHANGE_PASSWORD', '"1033"=>"Back", "1046"=>"Voltar"'::hstore );
SELECT insert_sentences_page( 'CONFIRM_NEW_PASSWORD', 'CHANGE_PASSWORD', '"1033"=>"Confirm New Password", "1046"=>"Cofirmar Nova Senha"'::hstore );
SELECT insert_sentences_page( 'PROVIDE_YOUR_CURRENT_PASSWORD', 'CHANGE_PASSWORD', '"1033"=>"Please provide your current password", "1046"=>"Por favor informe sua senha atual"'::hstore );
SELECT insert_sentences_page( 'PROVIDE_YOUR_NEW_PASSWORD', 'CHANGE_PASSWORD', '"1033"=>"Please provide your new password", "1046"=>"Por favor informe sua nova senha"'::hstore );
SELECT insert_sentences_page( 'NEW_AND_CONFIRM_PASSWORD_MUST_MATCH', 'CHANGE_PASSWORD', '"1033"=>"The new password and its confirmation must match", "1046"=>"A nova senha e sua confirmação devem ser idênticas"'::hstore );
SELECT insert_sentences_page( 'PASSWORD_CHANGED', 'CHANGE_PASSWORD', '"1033"=>"The has been changed", "1046"=>"A senha foi alterada"'::hstore );
SELECT insert_sentences_page( 'PASSWORD_LENGTH_ERROR', 'CHANGE_PASSWORD', '"1033"=>"The new nassword does not have the minimum length", "1046"=>"A nova senha não possui a quantidade mínima de caracteres"'::hstore );
SELECT insert_sentences_page( 'PASSWORD_COMPLEXITY', 'CHANGE_PASSWORD', '"1033"=>"The new nassword does not have meet the complexity requirements: it must contain at least one uppercase letter, one lowercase letter and one number", "1046"=>"A nova senha não possui a complexidade necessária: deve conter pelo menos uma letra maiúscula, uma letra minúscula e um número"'::hstore );
SELECT insert_sentences_page( 'INCORRECT_CURRENT_PASSWORD', 'CHANGE_PASSWORD', '"1033"=>"The current password is incorrect", "1046"=>"A senha atual está incorreta"'::hstore );
SELECT insert_sentences_page( 'NEW_PASSWORD_MUST_BE_DIFFERENT', 'CHANGE_PASSWORD', '"1033"=>"New password must not be the same as the current password", "1046"=>"A nova senha deve ser diferente da senha atual"'::hstore );
SELECT insert_sentences_page( 'RELOAD', 'USERS', '"1033"=>"Reload", "1046"=>"Recarregar"'::hstore );

--# TASKS
--#SELECT insert_task_status ( 'QUEUED', '"1033"=>"Queued", "1046"=>"Enfileirado"'::hstore );
SELECT insert_task_status ( 'IDENTIFYING', '"1033"=>"Identifying", "1046"=>"Identificando"'::hstore );
SELECT insert_task_status ( 'PREPARING', '"1033"=>"Preparing", "1046"=>"Preparando"'::hstore );
SELECT insert_task_status ( 'DONE', '"1033"=>"Done", "1046"=>"Concluído"'::hstore );
SELECT insert_task_status ( 'RETRYING', '"1033"=>"Retrying", "1046"=>"Tentando Novamente"'::hstore );
SELECT insert_task_status ( 'ERROR', '"1033"=>"Error", "1046"=>"Erro"'::hstore );

--# PAYMENT
SELECT insert_task_status ( 'PENDING', '"1033"=>"Payment Pending", "1046"=>"Aguardando Pagamento"'::hstore );

--# ADDING PERMISSIONS
SELECT insert_permission( 'ADD_ACCOUNT'::text, '"1033"=>"Permission to add accounts", "1046"=>"Permissão para adicionar contas"'::hstore );
SELECT insert_permission( 'CREATE_ACCOUNT'::text, '"1033"=>"Permission to create account", "1046"=>"Permissão para criar conta"'::hstore );
SELECT insert_permission( 'RECOVER_PASSWORD'::text, '"1033"=>"Permission to recover password", "1046"=>"Permissão para recuperar senha"'::hstore );
SELECT insert_permission( 'LINK_ACCOUNT'::text, '"1033"=>"Permission to access the likn account page", "1046"=>"Permissão para acessar a página de vinculação de conta"'::hstore );
SELECT insert_permission( 'CONTACT'::text, '"1033"=>"Permission to access the contact page", "1046"=>"Permissão para acessar a página de contato"'::hstore );
SELECT insert_permission( 'LANGUAGE'::text, '"1033"=>"Permission to change vizualization language", "1046"=>"Permissão para alterar o idioma de visualização"'::hstore );
SELECT insert_permission( 'SITE_INFORMATION'::text, '"1033"=>"Permission to view and edit site informations", "1046"=>"Permissão para acessar e editar informações do site"'::hstore );
SELECT insert_permission( 'SITE_MENU'::text, '"1033"=>"Permission to view and edit menu", "1046"=>"Permissão para acessar e editar menu do site"'::hstore );
SELECT insert_permission( 'POSTS'::text, '"1033"=>"Permission to view posts", "1046"=>"Permissão para ver publicações do site"'::hstore );
SELECT insert_permission( 'NEW_POST'::text, '"1033"=>"Permission to add posts", "1046"=>"Permissão para adicionar publicações do site"'::hstore );
SELECT insert_permission( 'CHANGE_PASSWORD'::text, '"1033"=>"Permission to change its own password", "1046"=>"Permissão para alterar sua própria senha"'::hstore );

--# ADDING MENUS
WITH A AS (
  SELECT  'HOME' AS alias,
          '0'::smallint AS menu_order,
          '"1033"=>"Home", "1046"=>"Início"'::hstore AS menu_content,
          '"1033"=>"permission to access home", "1046"=>"permissão para acessar o home"'::hstore AS permission_content,
          'home'::text AS menu_function,
          '"cover"=>"w-1.png"'::hstore AS menu_info
), B AS (
  INSERT INTO tb_sentences ( sentence_alias, sentence_value )
    SELECT CONCAT('MENU_',alias) AS alias, menu_content FROM A
    RETURNING sentence_id
), C AS (
  INSERT INTO tb_sentences ( sentence_alias, sentence_value )
    SELECT CONCAT('PERMISSION_',alias) AS alias, permission_content FROM A
    RETURNING sentence_id
), D AS (
  INSERT INTO tb_permissions ( permission_alias, permission_sentence_id )
    SELECT alias, sentence_id FROM A,C
    RETURNING permission_id
)
INSERT INTO tb_menu ( menu_alias, menu_sentence_id, menu_permission_id, menu_wui, menu_parent, menu_order, menu_function, menu_info )
  SELECT alias, sentence_id, permission_id, false, null, menu_order, menu_function, menu_info FROM A,B,D;

WITH A AS (
  SELECT  'MY_ACCOUNT' AS alias,
          '99'::smallint AS menu_order,
          '"1033"=>"My Account", "1046"=>"Minha Conta"'::hstore AS menu_content,
          '"1033"=>"permission to access to login or access my account", "1046"=>"permissão para autenticar ou acessar minha conta"'::hstore AS permission_content,
          '"icon"=>"ic-user"'::hstore AS menu_info,
          'my_account'::text AS menu_function
), B AS (
  INSERT INTO tb_sentences ( sentence_alias, sentence_value )
    SELECT CONCAT('MENU_',alias) AS alias, menu_content FROM A
    RETURNING sentence_id
), C AS (
  INSERT INTO tb_sentences ( sentence_alias, sentence_value )
    SELECT CONCAT('PERMISSION_',alias) AS alias, permission_content FROM A
    RETURNING sentence_id
), D AS (
  INSERT INTO tb_permissions ( permission_alias, permission_sentence_id ) 
    SELECT alias, sentence_id FROM A,C
    RETURNING permission_id
)
INSERT INTO tb_menu ( menu_alias, menu_sentence_id, menu_permission_id, menu_wui, menu_parent, menu_order, menu_info, menu_function ) 
  SELECT alias, sentence_id, permission_id, true, null, menu_order, menu_info, menu_function FROM A,B,D;

WITH A AS (
  SELECT  'USER_LOGOUT' AS alias,
          '99'::smallint AS menu_order,
          '"1033"=>"Logout", "1046"=>"Sair"'::hstore AS menu_content,
          '"1033"=>"permission to access to logout", "1046"=>"permissão para sair"'::hstore AS permission_content,
          'user_logout'::text AS menu_function
), B AS (
  INSERT INTO tb_sentences ( sentence_alias, sentence_value )
    SELECT CONCAT('MENU_',alias) AS alias, menu_content FROM A
    RETURNING sentence_id
), C AS (
  INSERT INTO tb_sentences ( sentence_alias, sentence_value )
    SELECT CONCAT('PERMISSION_',alias) AS alias, permission_content FROM A
    RETURNING sentence_id
), D AS (
  INSERT INTO tb_permissions ( permission_alias, permission_sentence_id ) 
    SELECT alias, sentence_id FROM A,C
    RETURNING permission_id
)
INSERT INTO tb_menu ( menu_alias, menu_sentence_id, menu_permission_id, menu_wui, menu_parent, menu_order, menu_function ) 
  SELECT alias, sentence_id, permission_id, true, (SELECT menu_id FROM tb_menu WHERE menu_alias='MY_ACCOUNT' LIMIT 1), menu_order, menu_function FROM A,B,D;

WITH A AS (
  SELECT  'MANAGE' AS alias,
          '90'::smallint AS menu_order,
          '"1033"=>"Manage", "1046"=>"Gerenciar"'::hstore AS menu_content,
          '"icon"=>"ic-settings"'::hstore AS menu_info,
          'manage'::text AS menu_function
), B AS (
  INSERT INTO tb_sentences ( sentence_alias, sentence_value )
    SELECT CONCAT('MENU_',alias) AS alias, menu_content FROM A
    RETURNING sentence_id
)
INSERT INTO tb_menu ( menu_alias, menu_sentence_id, menu_permission_id, menu_wui, menu_parent, menu_order, menu_info, menu_function ) 
  SELECT alias, sentence_id, null, true, null, menu_order, menu_info, menu_function FROM A,B;
    
  
WITH A AS (
  SELECT  'MANAGE_USERS' AS alias,
          '98'::smallint AS menu_order,
          '"1033"=>"Users", "1046"=>"Usuários"'::hstore AS menu_content,
          '"1033"=>"permission to use manage users tool", "1046"=>"permissão para usar a ferramenta de gerência de usuários"'::hstore AS permission_content,
          'manage_users'::text AS menu_function
        
), B AS (
  INSERT INTO tb_sentences ( sentence_alias, sentence_value )
    SELECT CONCAT('MENU_',alias) AS alias, menu_content FROM A
    RETURNING sentence_id
), C AS (
  INSERT INTO tb_sentences ( sentence_alias, sentence_value )
    SELECT CONCAT('PERMISSION_',alias) AS alias, permission_content FROM A
    RETURNING sentence_id
), D AS (
  INSERT INTO tb_permissions ( permission_alias, permission_sentence_id ) 
    SELECT alias, sentence_id FROM A,C
    RETURNING permission_id
)
INSERT INTO tb_menu ( menu_alias, menu_sentence_id, menu_permission_id, menu_wui, menu_parent, menu_order, menu_function ) 
  SELECT alias, sentence_id, permission_id, true, (SELECT menu_id FROM tb_menu WHERE menu_alias='MANAGE' LIMIT 1), menu_order, menu_function FROM A,B,D;

WITH A AS (
  SELECT  'SITE_CONFIGURATION' AS alias,
          '80'::smallint AS menu_order,
          '"1033"=>"Site", "1046"=>"Site"'::hstore AS menu_content,
          '"1033"=>"permission to access site configuration", "1046"=>"permissão para acessar a configuração do site"'::hstore AS permission_content,
          'site_configuration'::text AS menu_function
), B AS (
  INSERT INTO tb_sentences ( sentence_alias, sentence_value )
    SELECT CONCAT('MENU_',alias) AS alias, menu_content FROM A
    RETURNING sentence_id
), C AS (
  INSERT INTO tb_sentences ( sentence_alias, sentence_value )
    SELECT CONCAT('PERMISSION_',alias) AS alias, permission_content FROM A
    RETURNING sentence_id
), D AS (
  INSERT INTO tb_permissions ( permission_alias, permission_sentence_id ) 
    SELECT alias, sentence_id FROM A,C
    RETURNING permission_id
)
INSERT INTO tb_menu ( menu_alias, menu_sentence_id, menu_permission_id, menu_wui, menu_parent, menu_order, menu_function ) 
  SELECT alias, sentence_id, permission_id, true, (SELECT menu_id FROM tb_menu WHERE menu_alias='MANAGE' LIMIT 1), menu_order, menu_function FROM A,B,D; 
  
--# ADDING PANEL
WITH A AS (
  SELECT  'LOGIN' AS alias,
          '1'::smallint AS panel_order,
          '"1033"=>"Signin", "1046"=>"Login"'::hstore AS panel_content,
          '"1033"=>"Permission to access login panel", "1046"=>"permissão para acessar o login de busca"'::hstore AS permission_content,
          'ic-user'::text AS panel_icon,
          '1'::smallint as panel_floor
), B AS (
  INSERT INTO tb_sentences ( sentence_alias, sentence_value )
    SELECT CONCAT('PANEL_',alias) AS alias, panel_content FROM A
    RETURNING sentence_id
), C AS (
  INSERT INTO tb_sentences ( sentence_alias, sentence_value )
    SELECT CONCAT('PERMISSION_',alias) AS alias, permission_content FROM A
    RETURNING sentence_id
), D AS (
  INSERT INTO tb_permissions ( permission_alias, permission_sentence_id )
    SELECT alias, sentence_id FROM A,C
    RETURNING permission_id
)
INSERT INTO tb_panel ( panel_alias, panel_sentence_id, panel_permission_id, panel_order, panel_icon, panel_floor )
  SELECT alias, sentence_id, permission_id, panel_order, panel_icon, panel_floor FROM A,B,D;

WITH A AS (
  SELECT  'SEARCH' AS alias,
          '99'::smallint AS panel_order,
          '"1033"=>"Search", "1046"=>"Busca"'::hstore AS panel_content,
          '"1033"=>"Permission to access search panel", "1046"=>"permissão para acessar o painel de busca"'::hstore AS permission_content,
          'ic-search'::text AS panel_icon
), B AS (
  INSERT INTO tb_sentences ( sentence_alias, sentence_value )
    SELECT CONCAT('PANEL_',alias) AS alias, panel_content FROM A
    RETURNING sentence_id
), C AS (
  INSERT INTO tb_sentences ( sentence_alias, sentence_value )
    SELECT CONCAT('PERMISSION_',alias) AS alias, permission_content FROM A
    RETURNING sentence_id
), D AS (
  INSERT INTO tb_permissions ( permission_alias, permission_sentence_id )
    SELECT alias, sentence_id FROM A,C
    RETURNING permission_id
)
INSERT INTO tb_panel ( panel_alias, panel_sentence_id, panel_permission_id, panel_order, panel_icon )
  SELECT alias, sentence_id, permission_id, panel_order, panel_icon FROM A,B,D;
 
--# ADDING GROUPS
SELECT insert_group( 'USERS'::text, '"1033"=>"Simple users of the system", "1046"=>"Usuários simples do sistema"'::hstore, 10000::smallint );
SELECT insert_group( 'ADMINISTRATORS'::text, '"1033"=>"Administrators of the system", "1046"=>"Administradores do sistema"'::hstore, 30000::smallint );
SELECT insert_group( 'GUEST'::text, '"1033"=>"Guest access to the system", "1046"=>"Acesso convidado ao sistema"'::hstore, 1::smallint );
SELECT insert_group( 'PUBLISHER'::text, '"1033"=>"Publishers of content", "1046"=>"Publicadores de conteúdo"'::hstore, 11000::smallint );
SELECT insert_group( 'USERS_MANAGER'::text, '"1033"=>"Managers of users", "1046"=>"Gerenciadores de usuários"'::hstore, 20000::smallint );
  
 
--# ADDING USER STATES
WITH A AS (
  SELECT  'NORMAL' AS alias,
          '"1033"=>"user should be working fine", "1046"=>"usuário deveria estar funcionando normalmente"'::hstore AS content
), B AS (
  INSERT INTO tb_sentences ( sentence_alias, sentence_value ) SELECT CONCAT('STATE','_',alias), content FROM A
  RETURNING sentence_id )
INSERT INTO tb_user_states ( user_state_alias, user_state_description ) 
  SELECT alias, sentence_id FROM A,B;

WITH A AS (
  SELECT  'BLOCKED' AS alias,
          '"1033"=>"user is blocked", "1046"=>"usuário está bloqueado"'::hstore AS content
), B AS (
  INSERT INTO tb_sentences ( sentence_alias, sentence_value ) SELECT CONCAT('STATE','_',alias), content FROM A
  RETURNING sentence_id )
INSERT INTO tb_user_states ( user_state_alias, user_state_description ) 
  SELECT alias, sentence_id FROM A,B;
  
WITH A AS (
  SELECT  'BANNED' AS alias,
          '"1033"=>"user is banned", "1046"=>"usuário está banido"'::hstore AS content
), B AS (
  INSERT INTO tb_sentences ( sentence_alias, sentence_value ) SELECT CONCAT('STATE','_',alias), content FROM A
  RETURNING sentence_id )
INSERT INTO tb_user_states ( user_state_alias, user_state_description ) 
  SELECT alias, sentence_id FROM A,B;

WITH A AS (
  SELECT  'CONFIRM_EMAIL' AS alias,
          '"1033"=>"user must confirm its e-mail", "1046"=>"usuário deve confirmar seu e-mail"'::hstore AS content
), B AS (
  INSERT INTO tb_sentences ( sentence_alias, sentence_value ) SELECT CONCAT('STATE','_',alias), content FROM A
  RETURNING sentence_id )
INSERT INTO tb_user_states ( user_state_alias, user_state_description ) 
  SELECT alias, sentence_id FROM A,B;
  

--# GIVING PERMISSIONS TO GROUPS
select add_permission_to_group( 'GUEST'::text, '{"HOME","LANGUAGE","CONTACT","SEARCH","LINK_ACCOUNT","LOGIN","RECOVER_PASSWORD"}'::text[], true::boolean, true::boolean, true::boolean, true::boolean );
select add_permission_to_group( '{"USERS","ADMINISTRATORS"}'::text[], '{"HOME","LANGUAGE","CONTACT","SEARCH","LINK_ACCOUNT","USER_LOGOUT","MY_ACCOUNT","CHANGE_PASSWORD","POST","POSTS"}'::text[], true::boolean, true::boolean, true::boolean, true::boolean );

select add_permission_to_group( 'ADMINISTRATORS'::text, '{"ADD_ACCOUNT","SITE_CONFIGURATION","SITE_INFORMATION","MANAGE_USERS","SITE_MENU"}'::text[], true::boolean, true::boolean, true::boolean, true::boolean );

select add_permission_to_group( 'PUBLISHER'::text, 'NEW_POST'::text, true::boolean, true::boolean, true::boolean, true::boolean );

--# CREATING FIRST USER  
SELECT l1.language_name->CONCAT(language_codeset) AS name FROM tb_languages l1 WHERE l1.language_codeset=1033 ORDER BY name ASC;

INSERT INTO tb_users ( user_nickname, user_nickname_hashed, user_firstname, user_fullname, user_lastname, user_hash, user_primaryemail, user_primaryemail_hashed, user_language, user_attributes ) VALUES 
  ( 'fischer', 'd57ecf8cb794782c3ca67e5f027e9066988e82fa1110152945f2dd9991b3b1bd0ff8e4504deefb9d1b27158a84a8e08d32ac79562df6a3a914c4a5f71ba60e22', 'Leonardo', 'Leonardo Fischer', 'Fischer', 'f635eaca54e157faca879abf710473641136a99b5ed733ec1fd50402171bf9e3', 'leonardo@fischers.it', '0dfa1f12eece2404ccf726f0a74ef5cfce880a426a2dad0b6e8e5e4b5537ac1949b47e9976006ab597dd27d98b81b68a7c498ccb8d874fbf34f99fd548b47c51', 1, '"items_per_page"=>"50"' );
  
  
INSERT INTO tb_relation_user_group ( relation_user_group_user, relation_user_group_group ) VALUES 
  ( (SELECT user_id FROM tb_users WHERE user_nickname='fischer'), (SELECT group_id FROM tb_groups WHERE group_alias='ADMINISTRATORS') );
  
INSERT INTO tb_relation_user_group ( relation_user_group_user, relation_user_group_group ) VALUES 
  ( (SELECT user_id FROM tb_users WHERE user_nickname='fischer'), (SELECT group_id FROM tb_groups WHERE group_alias='USERS') );

INSERT INTO tb_relation_user_group ( relation_user_group_user, relation_user_group_group ) VALUES 
  ( (SELECT user_id FROM tb_users WHERE user_nickname='fischer'), (SELECT group_id FROM tb_groups WHERE group_alias='PUBLISHER') );
  
INSERT INTO tb_relation_user_state ( relation_user_state_user, relation_user_state_state ) VALUES 
  ( (SELECT user_id FROM tb_users WHERE user_nickname='fischer'), (SELECT user_state_id FROM tb_user_states WHERE user_state_alias='NORMAL') );
  
INSERT INTO tb_user_passwords ( user_password_user_id, user_password_hash ) VALUES ( (SELECT user_id FROM tb_users WHERE user_nickname='fischer'), '3d350e5d81198e315959112c2152e918c17a1710bb0cd6b5422214b2dbc9af667ce0a4d1833e7b0e021f2032c080781c2bdfb77026fe8086b148b77cdeb0c133' );
 
