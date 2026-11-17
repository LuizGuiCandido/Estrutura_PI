<?php

require_once __DIR__ . '/../Models/Conexao.php';

class LoginController extends Conexao
{
    public function __construct($db = null)
    {
        parent::__construct($db);
    }

    public function autenticar(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: Views/PaginaLogin.php');
            exit;
        }

        $email = trim((string) ($_POST['email'] ?? ''));
        $senha = (string) ($_POST['senha'] ?? '');

        if ($email === '' || $senha === '') {
            $this->erroLogin('Preencha o e-mail e a senha.');
        }

        $sql = "SELECT 
                    u.id_usuario,
                    u.nome,
                    u.email,
                    u.senha,
                    u.gestor,
                    p.usuario_id AS is_professor,
                    a.usuario_id AS is_aluno
                FROM usuarios u
                LEFT JOIN professores p ON p.usuario_id = u.id_usuario AND p.deletado_em IS NULL
                LEFT JOIN alunos a ON a.usuario_id = u.id_usuario AND a.deletado_em IS NULL
                WHERE u.email = ? AND u.deletado_em IS NULL
                LIMIT 1";

        $stm = $this->db->prepare($sql);
        $stm->execute([$email]);
        $usuario = $stm->fetch(PDO::FETCH_OBJ);

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
            header('Location: Views/Dashboard.php');
            exit;
        }

        if (!empty($usuario->is_professor)) {
            $_SESSION['perfil'] = 'professor';
            header('Location: Views/turmas.php');
            exit;
        }

        if (!empty($usuario->is_aluno)) {
            $_SESSION['perfil'] = 'aluno';
            header('Location: Views/aluno_perfil.php');
            exit;
        }

        session_unset();
        session_destroy();
        $this->erroLogin('Usuário sem perfil ativo. Procure a administração.');
    }

    public function sair(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];
        session_destroy();

        header('Location: Views/PaginaLogin.php');
        exit;
    }

    private function erroLogin(string $mensagem): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['erro_login'] = $mensagem;
        header('Location: Views/PaginaLogin.php');
        exit;
    }

    public function alterarSenha(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $idUsuario = (int) ($_SESSION['id_usuario'] ?? 0);
        if ($idUsuario <= 0) {
            header('Location: Views/PaginaLogin.php');
            exit;
        }

        $senhaAtual     = (string) ($_POST['senha_atual'] ?? '');
        $novaSenha      = (string) ($_POST['nova_senha'] ?? '');
        $confirmarSenha = (string) ($_POST['confirmar_senha'] ?? '');

        if ($novaSenha === '' || $novaSenha !== $confirmarSenha) {
            $_SESSION['erro'] = 'A nova senha e a confirmação não conferem.';
            header('Location: Views/aluno_perfil.php');
            exit;
        }

        $db = (new Conexao())->getConexao();
        $stm = $db->prepare("SELECT senha FROM usuarios WHERE id_usuario = ? AND deletado_em IS NULL");
        $stm->execute([$idUsuario]);
        $row = $stm->fetch(PDO::FETCH_OBJ);

        if (!$row || !password_verify($senhaAtual, $row->senha)) {
            $_SESSION['erro'] = 'A senha atual está incorreta.';
            header('Location: Views/aluno_perfil.php');
            exit;
        }

        $hash = password_hash($novaSenha, PASSWORD_DEFAULT);
        $upd = $db->prepare("UPDATE usuarios SET senha = ? WHERE id_usuario = ?");
        if ($upd->execute([$hash, $idUsuario])) {
            $_SESSION['sucesso'] = 'Senha alterada com sucesso!';
        } else {
            $_SESSION['erro'] = 'Não foi possível atualizar a senha.';
        }

        header('Location: Views/aluno_perfil.php');
        exit;
    }

    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Limpa todas as variáveis da sessão
        $_SESSION = [];

        // Remove o cookie de sessão do navegador, se existir
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        // Destrói a sessão no servidor
        session_destroy();

        // Redireciona para a tela de login
        header('Location: Views/PaginaLogin.php');
        exit;
    }


}