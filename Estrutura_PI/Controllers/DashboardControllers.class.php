<?php

require_once __DIR__ . '/Controller.class.php';

class DashboardController extends Controller
{
    public function index(): void
    {
        Permissao::exigirGestor();

        $dao = new DashboardDAO();

        $totalAlunos       = $dao->totalAlunos();
        $totalTurmas       = $dao->totalTurmasAtivas();
        $reposicoesSemana  = $dao->totalReposicoesMarcadas();
        $resumoPg          = $dao->resumoPagamentos();
        $ultimosPagamentos = $dao->ultimosPagamentos(5);
        $aulasHoje         = (new TurmaDAO())->listarGrade(6);
        $primeiroNome      = explode(' ', trim($_SESSION['nome'] ?? 'Gestor'))[0];

        $this->render('dashboard', compact(
            'totalAlunos', 'totalTurmas', 'reposicoesSemana', 'resumoPg',
            'ultimosPagamentos', 'aulasHoje', 'primeiroNome'
        ));
    }
}
