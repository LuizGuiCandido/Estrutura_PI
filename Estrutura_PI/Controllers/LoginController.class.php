<?php

require_once __DIR__ . '/Controller.class.php';

class LoginController extends Controller
{
    /** Exibe a tela de login. */
    public function entrar(): void
    {
        if (!empty($_SESSION['id_usuario'])) {
            header('Location: ' . Permissao::paginaInicial());
            exit;
        }

        $erro = $_SESSION['erro_login'] ?? null;
        unset($_SESSION['erro_login']);

        $this->render('login', compact('erro'));
    }

    public function autenticar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirecionar('LoginController', 'entrar');
        }

        $email = trim((string) ($_POST['email'] ?? ''));
        $senha = (string) ($_POST['senha'] ?? '');

        if ($email === '' || $senha === '') {
            $this->erroLogin('Preencha o e-mail e a senha.');
        }

        $usuario = (new UsuarioDAO())->buscarPorEmail($email);

        if (!$usuario || !password_verify($senha, $usuario->senha)) {
            $this->erroLogin('E-mail ou senha inválidos.');
        }

        session_regenerate_id(true);

        $_SESSION['id_usuario'] = (int) $usuario->id_usuario;
        $_SESSION['nome']       = $usuario->nome;
        $_SESSION['email']      = $usuario->email;
        $_SESSION['gestor']     = (int) $usuario->gestor;

        if ((int) $usuario->gestor === 1) {
            $_SESSION['perfil'] = 'gestor';
        } elseif (!empty($usuario->is_professor)) {
            $_SESSION['perfil'] = 'professor';
        } elseif (!empty($usuario->is_aluno)) {
            $_SESSION['perfil'] = 'aluno';
        } else {
            session_unset();
            session_destroy();
            session_start();
            $this->erroLogin('Usuário sem perfil ativo. Procure a administração.');
        }

        header('Location: ' . Permissao::paginaInicial());
        exit;
    }

    public function sair(): void
    {
        $_SESSION = [];
        session_destroy();

        $this->redirecionar('LoginController', 'entrar');
    }

    public function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }

        session_destroy();

        $this->redirecionar('LoginController', 'entrar');
    }

    public function alterarSenha(): void
    {
        Permissao::exigirLogin();

        $idUsuario      = $this->usuarioId();
        $senhaAtual     = (string) ($_POST['senha_atual'] ?? '');
        $novaSenha      = (string) ($_POST['nova_senha'] ?? '');
        $confirmarSenha = (string) ($_POST['confirmar_senha'] ?? '');

        if ($novaSenha === '' || $novaSenha !== $confirmarSenha) {
            $_SESSION['erro'] = 'A nova senha e a confirmação não conferem.';
            $this->redirecionar('AlunoController', 'perfil');
        }

        $dao  = new UsuarioDAO();
        $hash = $dao->buscarSenhaHash($idUsuario);

        if ($hash === null || !password_verify($senhaAtual, $hash)) {
            $_SESSION['erro'] = 'A senha atual está incorreta.';
            $this->redirecionar('AlunoController', 'perfil');
        }

        if ($dao->atualizarSenha($idUsuario, password_hash($novaSenha, PASSWORD_DEFAULT))) {
            $_SESSION['sucesso'] = 'Senha alterada com sucesso!';
        } else {
            $_SESSION['erro'] = 'Não foi possível atualizar a senha.';
        }

        $this->redirecionar('AlunoController', 'perfil');
    }

    private function erroLogin(string $mensagem): void
    {
        $_SESSION['erro_login'] = $mensagem;
        $this->redirecionar('LoginController', 'entrar');
    }
}
