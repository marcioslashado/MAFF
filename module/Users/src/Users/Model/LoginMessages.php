<?php
namespace Users\Model;

interface LoginMessages
{

    const INVALID_USER_PASSWORD = 'E-mail e/ou senha inválidos.';

    const LOGIN_LOCKED = 'Sua conta está temporariamente bloqueada devido a diversas tentativas de login incorretas. Por favor, redefina sua senha usando o link esqueci minha senha.';

    const ACCOUNT_NOT_ACTIVE = 'Sua conta não está ativa. Contacte o administrador do sistema.';

    const NOT_LOGIN_ACCESS = 'Faça login para continuar';

    const PASS_CHANGED_SUCCESS = 'Sua senha foi alterada com sucesso.';

    const INVALID_OLD_PASS = 'Sua senha antiga não é válida.';

    const RESET_SUCCESS_MESSAGE = 'Verifique se o seu e-mail para mais detalhes.';

    const EMAIL_NOT_EXIST = 'E-mail não existe. Por favor, tente novamente.';

    const RESET_TOKEN_EXPIRED = 'Sua senha expirou. Por favor, tente novamente.';

    const PASS_UPDATE_SUCCESS = 'Sua senha foi atualizada com sucesso.';

    const PASS_CHANGE_ERROR = 'Sua senha não pode ser atualizada. Entre em contato com o administrador do sistema.';

    const SELECT_ROLE_MESSAGE = 'Por favor, selecione um grupo.';

    const PASS_CHANGE_ROLE = "Seu grupo foi alterado com sucesso.";

    const PASS_SAME = "Sua nova senha não pode ser igual a antiga senha.";

    const ACCOUNT_EXPIRED = "A sua licença expirou. Por favor, entre em contato com a equipe.";

    const NO_ROLES = "Você não tem nenhum grupo. Entre em contato com o administrador do sistema.";

    const CSRF_ERROR = "O formulário submetido não se originou a partir do site esperado.";
}
