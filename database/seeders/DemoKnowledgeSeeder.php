<?php

namespace Database\Seeders;

use App\Jobs\GenerateChunkEmbedding;
use App\Models\Document;
use App\Services\TextChunker;
use Illuminate\Database\Seeder;

class DemoKnowledgeSeeder extends Seeder
{
    public function run(TextChunker $chunker): void
    {
        foreach ($this->documents() as $item) {
            $document = Document::create([
                'title' => $item['title'],
                'description' => $item['description'],
                'content' => $item['content'],
                'source_type' => 'text',
                'content_hash' => hash('sha256', $item['content']),
            ]);

            foreach ($chunker->chunk($document->content) as $position => $text) {
                $chunk = $document->chunks()->create([
                    'position' => $position,
                    'content' => $text,
                ]);

                GenerateChunkEmbedding::dispatch($chunk);
            }

            $this->command->info("Criado: {$document->title}");
        }
    }

    private function documents(): array
    {
        return [
            [
                'title' => 'Deploy para o ambiente do cliente',
                'description' => 'Procedimento manual de deploy, pré-requisitos, validação e rollback.',
                'content' => <<<'TXT'
O deploy para o ambiente do cliente é feito manualmente, sem pipeline automatizada. O acesso é por VPN e as credenciais estão no Vaultwarden, na colecção partilhada da equipa. Nunca partilhar credenciais por email ou chat.

Antes de qualquer deploy é obrigatório confirmar que não há jobs de importação a correr. Um deploy a meio de uma importação deixa registos parciais na tabela de staging e obriga a repor o snapshot anterior. A verificação faz-se na tabela de controlo de execuções, coluna de estado.

A janela acordada com o cliente é entre as 20h00 e as 23h00 de terça a quinta. Sextas-feiras estão excluídas por acordo, porque não há ninguém disponível no cliente para validar durante o fim-de-semana.

Depois do deploy, a validação é feita comparando o total de linhas processadas com o total de registos gravados. Se houver diferença, o processo deve ser revertido e o ficheiro analisado manualmente antes de nova tentativa.

Em caso de falha, o rollback consiste em repor o dump da base de dados tirado imediatamente antes do deploy. Os dumps de pré-deploy ficam retidos durante sete dias e depois são apagados automaticamente.

Cada deploy tem de ser registado no canal de operações com a versão, a hora de início e a hora de conclusão. Sem esse registo não é possível reconstruir a sequência de acontecimentos quando algo corre mal dias depois.
TXT,
            ],
            [
                'title' => 'Integração do feed SFTP de imputações',
                'description' => 'Origem, nomenclatura, modelo de carga e falhas conhecidas do ficheiro de imputações.',
                'content' => <<<'TXT'
O ficheiro de imputações chega diariamente por SFTP à pasta de entrada, enviado pelo sistema central do construtor. A ligação usa autenticação por chave, e a chave privada está no cofre da equipa. A rotação da chave é feita anualmente e coordenada por email com o contacto técnico do lado do construtor.

O nome do ficheiro segue um identificador fixo seguido da data no formato AAAAMMDD. Ficheiros com nome fora do padrão são ignorados silenciosamente pelo processo de leitura, o que já causou dois incidentes de dados em falta sem qualquer erro registado. Esta é a falha mais comum e a primeira coisa a verificar quando faltam dados de um dia.

O modelo de carga é de snapshot completo ao nível da ordem de reparação. Cada importação apaga os registos existentes dessa ordem e volta a inserir tudo o que vem no ficheiro. Não há carga incremental, por decisão do construtor, o que significa que uma linha removida na origem desaparece do nosso lado sem deixar rasto.

Os valores monetários vêm sem separador decimal e com duas casas implícitas. Um valor de 12345 no ficheiro corresponde a 123,45 euros. Este detalhe não está documentado do lado do construtor e foi descoberto por comparação com relatórios em papel.

Quando o ficheiro não chega até às 07h00, o processo não gera erro, apenas não corre. A ausência de ficheiro é indistinguível de um dia sem movimento, e por isso a validação diária é feita à mão.
TXT,
            ],
            [
                'title' => 'Ambiente de desenvolvimento local',
                'description' => 'Como montar o projecto numa máquina nova e problemas frequentes.',
                'content' => <<<'TXT'
O projecto corre localmente sem containers. Cada programador tem o servidor web e o PHP instalados directamente na máquina, e a base de dados corre como serviço local. A decisão de não usar containers foi tomada por causa da lentidão do sistema de ficheiros partilhado em máquinas mais antigas da equipa.

A versão de PHP tem de coincidir com a de produção. Versões diferentes causam diferenças subtis no tratamento de datas e na ordenação de arrays associativos, e já produziram bugs que só apareceram depois do deploy.

O ficheiro de configuração local não é versionado. Existe um exemplo no repositório que deve ser copiado e preenchido com as credenciais locais. Nunca commitar o ficheiro real, mesmo com as palavras-passe apagadas, porque o histórico do repositório mantém tudo.

A base de dados local é povoada a partir de um dump anonimizado que está no armazenamento partilhado. O dump é actualizado mensalmente. Não usar dumps de produção sem anonimizar, mesmo em máquinas pessoais.

O problema mais frequente numa instalação nova é o limite de memória do PHP. O valor por omissão não chega para os relatórios maiores e o sintoma é uma página em branco sem mensagem de erro nenhuma. A solução é aumentar o limite na configuração local.
TXT,
            ],
            [
                'title' => 'Convenções de base de dados e política de migrations',
                'description' => 'Nomenclatura de tabelas e colunas, e regras para alterar o esquema.',
                'content' => <<<'TXT'
Os nomes de tabelas são no plural e em minúsculas, com palavras separadas por underscore. As colunas de chave estrangeira são o nome da tabela no singular seguido de underscore e id. Esta convenção existe desde a reescrita de 2019 e as tabelas anteriores a essa data não a seguem, o que obriga a configuração explícita nesses casos.

Nenhuma alteração ao esquema é feita directamente na base de dados, nem em desenvolvimento nem em produção. Tudo passa por migrations versionadas no repositório. Uma alteração feita à mão numa máquina cria divergência silenciosa que só aparece semanas depois, quando outra pessoa não consegue reproduzir um erro.

Migrations que já correram em produção nunca são editadas. Se for preciso corrigir alguma coisa, cria-se uma migration nova. Editar uma migration antiga funciona na máquina de quem edita e falha em todas as outras, porque o registo de execução já existe.

Colunas de data e hora são sempre guardadas em UTC e convertidas na apresentação. Houve um período em que se guardava hora local, e os registos desse período têm um desvio de uma hora durante o horário de Verão que nunca foi corrigido.

Não usar apagamento físico em tabelas com histórico contabilístico. A remoção é lógica, com uma coluna de data de remoção, por exigência de auditoria do cliente.
TXT,
            ],
            [
                'title' => 'Workarounds no código legado de facturação',
                'description' => 'Porque é que o módulo de facturação tem código estranho e o que não se pode tocar.',
                'content' => <<<'TXT'
O módulo de facturação tem várias soluções de recurso que parecem erros mas são deliberadas. Este documento existe para evitar que alguém as remova por as achar redundantes.

O arredondamento é feito linha a linha e não no total. Matematicamente produz um resultado diferente de arredondar no fim, e é frequente alguém tentar corrigir isso. Não corrigir. O sistema do cliente arredonda da mesma forma e qualquer alteração cria diferenças de cêntimos que geram reconciliações manuais todos os meses.

A numeração de documentos usa um bloqueio explícito na tabela de séries, em vez de uma sequência da base de dados. A razão é legal: a numeração tem de ser contínua e sem saltos, e uma sequência da base de dados salta números quando uma transacção é revertida.

Existe uma cópia da morada do cliente gravada em cada factura, em vez de uma referência à ficha do cliente. É duplicação de dados intencional. A factura tem de reflectir a morada à data da emissão, e não a morada actual.

O cálculo de IVA passa por uma tabela de excepções por tipo de serviço. A tabela tem entradas que já não se aplicam a serviços novos mas que têm de continuar a existir para reprocessar facturas antigas.
TXT,
            ],
            [
                'title' => 'Autenticação e sessões partilhadas com o portal do cliente',
                'description' => 'Como funciona o início de sessão vindo do portal e as suas limitações.',
                'content' => <<<'TXT'
Os utilizadores entram na aplicação a partir do portal do cliente, sem criar conta do nosso lado. O portal envia um token assinado que validamos com a chave pública que nos foi entregue na integração inicial.

O token tem validade de cinco minutos e só pode ser usado uma vez. Guardamos os identificadores já consumidos durante uma hora para impedir reutilização. Passada essa hora o registo é limpo, porque o token já expirou por si.

Se o token for válido mas o utilizador não existir do nosso lado, é criado automaticamente com o perfil mais restrito. A atribuição de permissões adicionais é sempre manual e feita por um administrador, nunca inferida do que vem no token.

A sessão do nosso lado dura oito horas e não é renovada por actividade. Quando expira, o utilizador é reencaminhado para o portal e volta a entrar. Foi uma exigência de segurança do cliente e já houve pedidos para aumentar, todos recusados.

Não existe recuperação de palavra-passe na nossa aplicação porque não guardamos palavras-passe nenhumas. Pedidos de utilizadores nesse sentido são encaminhados para o suporte do portal.
TXT,
            ],
            [
                'title' => 'Backups e reposição da base de dados',
                'description' => 'Periodicidade, retenção, e o procedimento de restauro testado.',
                'content' => <<<'TXT'
Existem dois tipos de cópia de segurança. A cópia completa corre todas as noites às 02h00 e fica retida durante trinta dias. As cópias de pré-deploy são manuais, feitas por quem faz o deploy, e ficam retidas sete dias.

As cópias são guardadas em armazenamento fora do servidor de aplicação. Uma cópia guardada na mesma máquina não protege contra a falha mais provável, que é a perda da própria máquina.

O restauro é testado trimestralmente numa máquina separada. O teste consiste em repor a cópia mais recente e confirmar que a aplicação arranca e que os totais de facturação do último mês fechado batem certo. Sem este teste, uma cópia corrompida só é descoberta no pior momento possível.

O tempo de reposição medido no último teste foi de cerca de quarenta minutos para a base completa. Este valor deve ser comunicado ao cliente quando ele pergunta quanto tempo demora a recuperar de um desastre.

As cópias não são cifradas em repouso. É uma lacuna conhecida e está na lista de melhorias, sem data atribuída.
TXT,
            ],
            [
                'title' => 'Turnos de apoio e gestão de incidentes',
                'description' => 'Quem responde fora de horas, como se classifica a gravidade e o que se escreve depois.',
                'content' => <<<'TXT'
O apoio fora de horas funciona por escala semanal, de segunda a segunda. A escala é publicada no início de cada mês e as trocas são combinadas directamente entre as pessoas envolvidas, bastando avisar o canal.

A gravidade é classificada em três níveis. Grave é a aplicação inacessível ou dados incorrectos a serem gravados. Média é uma funcionalidade importante em baixo com alternativa manual possível. Baixa é tudo o resto e espera pelo horário normal.

Só incidentes graves justificam contacto fora de horas. Um incidente médio comunicado às duas da manhã não vai ser resolvido melhor do que às nove, e desgasta a pessoa de serviço sem benefício nenhum.

Todos os incidentes graves têm análise posterior escrita, feita nos dois dias úteis seguintes. A análise descreve o que aconteceu, porque aconteceu, e que alteração impede a repetição. Não atribui culpa a pessoas, porque isso faz com que a próxima pessoa esconda o problema em vez de o comunicar.

As análises ficam num documento partilhado e são revistas de três em três meses para verificar se as acções acordadas foram feitas.
TXT,
            ],
            [
                'title' => 'Particularidades do cliente Verdauto',
                'description' => 'Excepções de configuração e comportamento específicas deste cliente.',
                'content' => <<<'TXT'
O cliente Verdauto tem várias excepções em relação ao comportamento normal da aplicação, e quase todos os relatos de erro deste cliente acabam por ser uma destas excepções.

As oficinas deste cliente trabalham ao sábado de manhã. O cálculo de prazos de resposta tem uma configuração própria que conta sábado como dia útil parcial, e por isso os prazos apresentados diferem dos outros clientes.

O ficheiro de exportação para a contabilidade deles usa ponto e vírgula como separador e codificação Latin-1, e não vírgula e UTF-8 como todos os outros. Alterar isto parte a importação do lado deles, que corre num sistema antigo sem manutenção.

Este cliente exige que os anexos das ordens de reparação fiquem retidos durante dez anos, contra os cinco anos por omissão. A configuração de retenção é por cliente e é verificada pelo processo de limpeza nocturno.

Os utilizadores da Verdauto não têm acesso ao módulo de stock. Foi opção deles, porque usam outro sistema para isso, e o menu está escondido por configuração e não por permissões, o que confunde quem investiga um pedido de acesso.
TXT,
            ],
            [
                'title' => 'Regras de revisão de código',
                'description' => 'O que se espera de quem submete e de quem revê alterações.',
                'content' => <<<'TXT'
Nenhuma alteração entra no ramo principal sem revisão de outra pessoa. A regra aplica-se também a correcções pequenas, porque a maior parte dos incidentes recentes veio de alterações de uma linha feitas com pressa.

Quem submete tem de descrever o que a alteração faz e porquê. Uma descrição que só repete o título do bilhete obriga o revisor a reconstruir o contexto e torna a revisão mais lenta e pior.

Submissões grandes são o principal obstáculo a revisões úteis. Acima de umas centenas de linhas alteradas, a qualidade da revisão cai bruscamente e o revisor limita-se a aprovar. Partir o trabalho em alterações menores é responsabilidade de quem submete.

Comentários de revisão distinguem o que é bloqueante do que é sugestão. Sem essa distinção, quem recebe não sabe o que tem de mudar e discute-se estilo enquanto passa um erro real.

Não se revê código próprio nem se aprova a própria alteração usando outra conta. Já aconteceu, foi detectado no histórico, e é motivo de conversa e não de sanção.
TXT,
            ],
            [
                'title' => 'Estados do quadro de oficina',
                'description' => 'Significado de cada estado do quadro e regras de transição.',
                'content' => <<<'TXT'
O quadro representa o percurso de uma viatura dentro da oficina e cada coluna é um estado. As transições não são livres: há regras que impedem saltos que não fazem sentido operacional.

Uma entrada começa em recepção, onde só existe a viatura e o motivo declarado pelo cliente. Passa a diagnóstico quando um técnico assume o trabalho. Não pode passar directamente de recepção para reparação, porque sem diagnóstico não há orçamento.

Depois do diagnóstico, a entrada fica à espera de aprovação do cliente. Este é o estado onde as viaturas ficam mais tempo paradas e é o principal indicador de problemas de comunicação com o cliente.

Existe um estado intermédio de espera de peças, que se distingue de aguardar aprovação. Confundir os dois faz com que ninguém saiba se está à espera de uma decisão ou de material, e foi essa confusão que motivou a separação dos estados.

Uma entrada sem data de marcação atribuída é assinalada visualmente, porque significa que o trabalho está a ocupar espaço na oficina sem estar planeado. É um sinal operacional e não um erro de dados.
TXT,
            ],
            [
                'title' => 'Integração com o catálogo de peças',
                'description' => 'Como se consultam referências de peças e limites do serviço externo.',
                'content' => <<<'TXT'
As referências de peças são consultadas num serviço externo do fornecedor, por chamada HTTP com autenticação por chave. A chave é única por instalação e não deve ser partilhada entre ambientes, porque o consumo é contabilizado por chave.

O serviço tem um limite de mil consultas por hora. Acima disso devolve um erro específico e bloqueia durante quinze minutos. Por causa desse limite, as respostas são guardadas em cache durante vinte e quatro horas.

Os preços devolvidos não incluem impostos nem descontos contratuais. O cálculo do preço final é feito do nosso lado, aplicando a tabela de descontos negociada por oficina. Mostrar o preço do catálogo directamente ao cliente final é erro.

Referências descontinuadas continuam a ser devolvidas pelo serviço, com um indicador próprio. É preciso verificar esse indicador, senão orçamentam-se peças que já não é possível encomendar.

Quando o serviço externo está indisponível, a aplicação continua a funcionar com os dados em cache e assinala que os preços podem estar desactualizados. Nunca bloquear a criação de um orçamento por causa da indisponibilidade do catálogo.
TXT,
            ],
            [
                'title' => 'Envio de emails transaccionais',
                'description' => 'Configuração de envio, reputação de domínio e tratamento de devoluções.',
                'content' => <<<'TXT'
Os emails transaccionais são enviados através de um serviço externo e não pelo servidor de aplicação. Enviar directamente do servidor coloca as mensagens em pastas de lixo por falta de reputação do endereço IP.

O domínio de envio tem registos de autenticação configurados. Qualquer alteração a esses registos tem de ser coordenada com quem gere o domínio do cliente, e uma alteração mal feita derruba a entrega de todo o correio durante horas.

As devoluções são recebidas por webhook e registadas. Um endereço com devoluções permanentes é marcado e deixa de receber envios, porque insistir em endereços inválidos degrada a reputação de todo o domínio.

Não são enviados emails a partir do ambiente de desenvolvimento. A configuração local aponta para uma caixa de captura que mostra as mensagens numa interface web sem as entregar a ninguém. Já houve um caso de emails de teste enviados a clientes reais e é a razão desta regra.

Os anexos são limitados a dez megabytes. Acima disso é enviado um link de descarregamento com validade de sete dias.
TXT,
            ],
            [
                'title' => 'Optimização do relatório de facturação mensal',
                'description' => 'Porque era lento, o que foi mudado e o que não se deve reverter.',
                'content' => <<<'TXT'
O relatório de facturação mensal demorava mais de dois minutos e frequentemente excedia o tempo limite do servidor web. A investigação mostrou que o problema não estava na quantidade de dados mas no número de consultas.

O código percorria cada linha de factura e ia buscar o cliente e a oficina em consultas separadas. Com alguns milhares de linhas, isso resultava em dezenas de milhares de consultas para um relatório de uma página. É o problema clássico de consultas em ciclo.

A correcção foi carregar as relações antecipadamente numa única consulta adicional. O tempo passou de dois minutos para menos de três segundos, sem alterar uma linha do cálculo.

Foi acrescentado um índice composto sobre a data de emissão e o identificador de oficina, que é a combinação usada em todos os filtros do relatório. Não remover esse índice: sem ele o tempo volta a subir para dezenas de segundos.

O relatório passou a ser gerado em segundo plano quando o intervalo pedido é superior a três meses, com notificação no fim. Abaixo desse intervalo continua a ser gerado no momento, porque a espera é aceitável e a experiência é melhor.
TXT,
            ],
            [
                'title' => 'Onboarding de novos programadores',
                'description' => 'Primeiros dias, acessos necessários e ordem sugerida de leitura.',
                'content' => <<<'TXT'
Nos primeiros dias o objectivo não é produzir código, é conseguir correr a aplicação localmente e perceber o percurso de uma ordem de reparação do início ao fim. Quem tenta escrever código antes disso perde mais tempo do que ganha.

Os acessos necessários são pedidos no primeiro dia e demoram tipicamente dois a três dias úteis: repositório, cofre de credenciais, canal da equipa, VPN e acesso de leitura ao ambiente de testes. O acesso ao ambiente do cliente só é dado depois do primeiro mês.

A primeira tarefa atribuída é sempre uma correcção pequena com impacto real, e não um exercício artificial. Uma alteração verdadeira que chega a produção na primeira semana ensina mais sobre o processo do que qualquer documentação.

Cada pessoa nova tem um acompanhante designado durante o primeiro mês, cuja função é responder a perguntas sem que a pessoa tenha de decidir a quem perguntar. Perguntar cedo é explicitamente encorajado.

Ao fim do primeiro mês há uma conversa de balanço centrada no que faltou na documentação. As respostas dessa conversa são a principal fonte de melhorias a este material, porque quem já cá está há anos não vê o que falta.
TXT,
            ],
        ];
    }
}
