<?php
session_start(); // Usado para mensagens de feedback (opcional)
include 'conexao.php'; // Inclui sua conexão com o banco

// --- INICIALIZAÇÃO DE VARIÁVEIS ---
$id = 0;
$titulo = '';
$autor = '';
$ano = '';
$genero = '';
$quantidade = 1;
$edit_mode = false; // Controla se o formulário está em modo de edição

// --- LÓGICA DO CRUD ---

// C (CREATE) - Criar novo livro
if (isset($_POST['create'])) {
    $titulo = $_POST['titulo'];
    $autor = $_POST['autor'];
    $ano = $_POST['ano'];
    $genero = $_POST['genero'];
    $quantidade = $_POST['quantidade'];

    $stmt = $mysqli->prepare("INSERT INTO livro (titulo, autor, ano, genero, quantidade) VALUES (?, ?, ?, ?, ?)");
    // s = string, i = integer
    $stmt->bind_param("ssisi", $titulo, $autor, $ano, $genero, $quantidade);
    $stmt->execute();
    
    header('Location: livros_crud.php'); // Redireciona para evitar reenvio
    exit;
}

// R (READ) - Obter dados para Edição
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $edit_mode = true;

    $stmt = $mysqli->prepare("SELECT * FROM livro WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $livro = $result->fetch_assoc();
        $titulo = $livro['titulo'];
        $autor = $livro['autor'];
        $ano = $livro['ano'];
        $genero = $livro['genero'];
        $quantidade = $livro['quantidade'];
    }
}

// U (UPDATE) - Atualizar livro
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $titulo = $_POST['titulo'];
    $autor = $_POST['autor'];
    $ano = $_POST['ano'];
    $genero = $_POST['genero'];
    $quantidade = $_POST['quantidade'];

    $stmt = $mysqli->prepare("UPDATE livro SET titulo = ?, autor = ?, ano = ?, genero = ?, quantidade = ? WHERE id = ?");
    $stmt->bind_param("ssisii", $titulo, $autor, $ano, $genero, $quantidade, $id);
    $stmt->execute();

    header('Location: livros_crud.php');
    exit;
}

// D (DELETE) - Deletar livro
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    // CUIDADO: Se este livro estiver em um 'emprestimo',
    // a FOREIGN KEY vai impedir a exclusão.
    // Você precisaria deletar os empréstimos dele primeiro.
    $stmt = $mysqli->prepare("DELETE FROM livro WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header('Location: livros_crud.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>CRUD de Livros</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { display: flex; gap: 40px; }
        .form-section { flex: 1; }
        .list-section { flex: 2; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        form div { margin-bottom: 10px; }
        label { display: block; margin-bottom: 5px; }
        input[type="text"], input[type="number"] { width: 100%; padding: 8px; box-sizing: border-box; }
        button { padding: 10px 15px; background-color: #007bff; color: white; border: none; cursor: pointer; }
        button.update { background-color: #28a745; }
        button.delete { background-color: #dc3545; padding: 5px 10px; }
        a { text-decoration: none; color: #007bff; }
        a.delete { color: #dc3545; margin-left: 10px; }
    </style>
</head>
<body>

    <h1>Gerenciamento de Livros</h1>

    <div class="container">
        
        <div class="form-section">
            <h2><?php echo $edit_mode ? 'Editar Livro' : 'Adicionar Novo Livro'; ?></h2>
            <form action="livros_crud.php" method="POST">
                
                <input type="hidden" name="id" value="<?php echo $id; ?>">

                <div>
                    <label>Título:</label>
                    <input type="text" name="titulo" value="<?php echo $titulo; ?>" required>
                </div>
                <div>
                    <label>Autor:</label>
                    <input type="text" name="autor" value="<?php echo $autor; ?>" required>
                </div>
                <div>
                    <label>Ano:</label>
                    <input type="number" name="ano" value="<?php echo $ano; ?>">
                </div>
                <div>
                    <label>Gênero:</label>
                    <input type="text" name="genero" value="<?php echo $genero; ?>">
                </div>
                <div>
                    <label>Quantidade:</label>
                    <input type="number" name="quantidade" value="<?php echo $quantidade; ?>" min="0" required>
                </div>
                <div>
                    <?php if ($edit_mode): ?>
                        <button type="submit" name="update" class="update">Atualizar</button>
                    <?php else: ?>
                        <button type="submit" name="create">Salvar</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="list-section">
            <h2>Lista de Livros</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Título</th>
                        <th>Autor</th>
                        <th>Ano</th>
                        <th>Qtd.</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // R (READ) - Listar todos os livros
                    $result = $mysqli->query("SELECT * FROM livro");
                    while ($livro = $result->fetch_assoc()):
                    ?>
                        <tr>
                            <td><?php echo $livro['id']; ?></td>
                            <td><?php echo $livro['titulo']; ?></td>
                            <td><?php echo $livro['autor']; ?></td>
                            <td><?php echo $livro['ano']; ?></td>
                            <td><?php echo $livro['quantidade']; ?></td>
                            <td>
                                <a href="livros_crud.php?edit=<?php echo $livro['id']; ?>">Editar</a>
                                <a href="livros_crud.php?delete=<?php echo $livro['id']; ?>" 
                                   class="delete" 
                                   onclick="return confirm('Tem certeza que deseja deletar este livro?');">
                                   Deletar
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>