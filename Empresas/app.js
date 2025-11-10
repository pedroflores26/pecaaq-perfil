// Função para listar produtos
async function listarProdutos() {
  const resp = await fetch('listarProdutos.php');
  const produtos = await resp.json();
  const container = document.getElementById('listaProdutos');
  container.innerHTML = '';

  produtos.forEach(prod => {
    const div = document.createElement('div');
    div.className = 'produtoCard';
    div.innerHTML = `
      <img src="uploads/${prod.foto_principal}" alt="${prod.nome}">
      <h3>${prod.nome}</h3>
      <p><strong>SKU:</strong> ${prod.sku_universal || '-'}</p>
      <p><strong>Marca:</strong> ${prod.marca || '-'}</p>
      <p><strong>Preço:</strong> R$ ${prod.preco}</p>
    `;
    container.appendChild(div);
  });
}

window.addEventListener('load', listarProdutos);
document.addEventListener('DOMContentLoaded', () => {
  const botoes = document.querySelectorAll('.addCarrinho');

  botoes.forEach(botao => {
    botao.addEventListener('click', (e) => {
      const card = e.target.closest('.card');
      const idAnuncio = card.querySelector('.id_anuncio').value;

      if (!idAnuncio) {
        alert("ID do anúncio não encontrado!");
        return;
      }

      // Aqui você faz a ação de adicionar ao carrinho
      console.log("Produto adicionado ao carrinho: ", idAnuncio);
      // Se tiver backend, pode fazer fetch para salvar no carrinho
    });
  });
});