

    "use strict";


/* =========================================================
   BANCO DE NOTÍCIAS DO BLOG
========================================================= */

const noticias = [

    {
        id: "resgate-gatos-vietna",

        titulo:
            "Mais de 400 gatos são resgatados de rede ilegal no Vietnã",

        categoria: "resgate",

        categoriasSecundarias: [
            "gatos"
        ],

        categoriaNome: "Resgate",

        categoriaIcone:
            "bi bi-heart-fill",

        data:
            "17 de junho de 2026",

        dataISO:
            "2026-06-17",

        tempoLeitura:
            "4 minutos",

        fonte:
            "Associated Press",

        fonteUrl:
            "https://apnews.com/article/ca0ce50281dbb5a624abf5cc95355e0f",

        imagem:
            "https://images.unsplash.com/photo-1573865526739-10659fec78a5?auto=format&fit=crop&w=1500&q=85",

        imagemAlt:
            "Gato olhando para a câmera",

        destaque: true,

        resumo:
            "Uma operação policial em Ho Chi Minh City encontrou centenas de gatos mantidos em condições precárias e permitiu que alguns deles fossem reencontrados por suas famílias.",

        citacao:
            "O resgate mobilizou policiais, veterinários, voluntários e famílias que procuravam seus animais desaparecidos.",

        tags: [
            "Gatos",
            "Resgate animal",
            "Vietnã",
            "Proteção animal"
        ],

        conteudo: [

            {
                tipo: "paragrafo",

                texto:
                    "Uma grande operação policial realizada na cidade de Ho Chi Minh, no Vietnã, resgatou mais de 400 gatos que estavam sob o controle de uma rede investigada por roubo e comércio ilegal de animais."
            },

            {
                tipo: "paragrafo",

                texto:
                    "Os animais foram encontrados em condições muito difíceis. Depois da operação, uma estrutura temporária foi preparada para que veterinários e voluntários pudessem oferecer alimentação, avaliação médica e cuidados emergenciais."
            },

            {
                tipo: "titulo",

                texto:
                    "Famílias começaram a procurar seus animais"
            },

            {
                tipo: "paragrafo",

                texto:
                    "As autoridades convidaram pessoas que haviam perdido seus gatos a comparecerem ao local para tentar identificá-los. Pelo menos dezenas de animais conseguiram reencontrar seus tutores nos primeiros dias após o resgate."
            },

            {
                tipo: "citacao",

                texto:
                    "O caso mostrou como animais domésticos podem se tornar vítimas de redes de captura, furto e comércio ilegal."
            },

            {
                tipo: "paragrafo",

                texto:
                    "Organizações de proteção animal acompanharam o caso e defenderam o fortalecimento das leis de proteção aos animais de companhia. O comércio de carne de cães e gatos ainda existe em determinadas regiões do país, embora enfrente crescente rejeição da população."
            },

            {
                tipo: "titulo",

                texto:
                    "A importância da identificação"
            },

            {
                tipo: "paragrafo",

                texto:
                    "O episódio também reforça a importância de medidas como microchipagem, plaquinhas de identificação, telas de proteção e supervisão dos animais. Esses cuidados facilitam o reencontro em situações de desaparecimento ou resgate."
            },

            {
                tipo: "paragrafo",

                texto:
                    "A matéria da Garra & Patas é um resumo informativo baseado na reportagem original da Associated Press. Para detalhes adicionais e atualizações, consulte a fonte indicada ao final da página."
            }

        ]

    },


    {
        id: "beagles-resgatados",

        titulo:
            "1.500 beagles deixam instalação de pesquisa e são preparados para adoção",

        categoria: "resgate",

        categoriasSecundarias: [
            "cachorros"
        ],

        categoriaNome: "Resgate",

        categoriaIcone:
            "bi bi-heart-fill",

        data:
            "29 de abril de 2026",

        dataISO:
            "2026-04-29",

        tempoLeitura:
            "5 minutos",

        fonte:
            "Associated Press",

        fonteUrl:
            "https://apnews.com/article/75efa4aa05cd4dff7575590de1610d7c",

        imagem:
            "https://images.unsplash.com/photo-1505628346881-b72b27e84530?auto=format&fit=crop&w=1500&q=85",

        imagemAlt:
            "Cachorro da raça beagle",

        destaque: false,

        resumo:
            "Organizações de proteção animal iniciaram uma grande operação para transportar, cuidar e encontrar famílias para aproximadamente 1.500 beagles.",

        citacao:
            "Os cães passaram por avaliações veterinárias, vacinação e microchipagem antes de seguirem para adoção responsável.",

        tags: [
            "Beagles",
            "Adoção",
            "Cachorros",
            "Bem-estar animal"
        ],

        conteudo: [

            {
                tipo: "paragrafo",

                texto:
                    "Aproximadamente 1.500 cães da raça beagle começaram uma nova etapa de suas vidas depois de serem retirados de uma instalação de criação e pesquisa localizada no estado de Wisconsin, nos Estados Unidos."
            },

            {
                tipo: "paragrafo",

                texto:
                    "A operação foi organizada por grupos de proteção animal, responsáveis por receber, transportar e distribuir os cães entre diferentes abrigos e centros de acolhimento."
            },

            {
                tipo: "titulo",

                texto:
                    "Preparação para uma vida em família"
            },

            {
                tipo: "paragrafo",

                texto:
                    "Antes da adoção, os animais precisam passar por exames veterinários, vacinação, microchipagem e avaliação comportamental. Esse processo ajuda as organizações a compreenderem as necessidades de cada cachorro."
            },

            {
                tipo: "citacao",

                texto:
                    "Uma adoção responsável considera o histórico, a saúde, o comportamento e o tempo de adaptação de cada animal."
            },

            {
                tipo: "paragrafo",

                texto:
                    "Muitos cães que viveram por longos períodos em ambientes controlados podem estranhar sons, superfícies, escadas, brinquedos e a rotina de uma casa. Por isso, paciência e acompanhamento são essenciais."
            },

            {
                tipo: "titulo",

                texto:
                    "Grande interesse pela adoção"
            },

            {
                tipo: "paragrafo",

                texto:
                    "As organizações envolvidas receberam centenas de manifestações de interesse. Mesmo com a grande procura, os responsáveis afirmaram que as famílias seriam avaliadas para garantir que os cães fossem encaminhados a ambientes seguros."
            },

            {
                tipo: "paragrafo",

                texto:
                    "O caso ganhou atenção internacional e estimulou novas discussões sobre proteção animal, criação comercial e uso de cães em pesquisas."
            }

        ]

    },


    {
        id: "evacuacao-pets-grecia",

        titulo:
            "Grécia organiza voo especial para evacuar famílias e 45 animais de estimação",

        categoria: "resgate",

        categoriasSecundarias: [
            "gatos",
            "cachorros"
        ],

        categoriaNome: "Resgate",

        categoriaIcone:
            "bi bi-heart-fill",

        data:
            "18 de março de 2026",

        dataISO:
            "2026-03-18",

        tempoLeitura:
            "4 minutos",

        fonte:
            "Associated Press",

        fonteUrl:
            "https://apnews.com/article/cf3bbc33c2d8c7c962dac34df4ebf8bd",

        imagem:
            "https://images.unsplash.com/photo-1558788353-f76d92427f16?auto=format&fit=crop&w=1500&q=85",

        imagemAlt:
            "Cachorro viajando com sua família",

        destaque: false,

        resumo:
            "Um voo organizado pelo governo grego transportou 101 pessoas e 45 animais que enfrentavam dificuldades para deixar uma região afetada por conflitos.",

        citacao:
            "A missão destacou que animais de companhia fazem parte das famílias e também precisam ser considerados em planos de emergência.",

        tags: [
            "Evacuação",
            "Cães e gatos",
            "Família",
            "Proteção"
        ],

        conteudo: [

            {
                tipo: "paragrafo",

                texto:
                    "O governo da Grécia organizou um voo especial para retirar cidadãos e seus animais de estimação de uma região afetada por conflitos e interrupções no transporte aéreo."
            },

            {
                tipo: "paragrafo",

                texto:
                    "A aeronave transportou 101 pessoas e 45 animais, incluindo cães e gatos. A chegada ao aeroporto de Atenas foi marcada pelo reencontro das famílias com parentes e pela saída dos animais de suas caixas de transporte."
            },

            {
                tipo: "titulo",

                texto:
                    "Animais também fazem parte do planejamento"
            },

            {
                tipo: "paragrafo",

                texto:
                    "Durante emergências, muitas pessoas enfrentam dificuldades para encontrar voos, abrigos ou transportes que aceitem animais. A missão grega chamou atenção justamente por incluir os pets no planejamento oficial."
            },

            {
                tipo: "citacao",

                texto:
                    "Em situações de emergência, proteger uma família também significa considerar os animais que fazem parte dela."
            },

            {
                tipo: "paragrafo",

                texto:
                    "Especialistas em proteção animal recomendam que tutores mantenham uma caixa de transporte adequada, cópias de documentos veterinários, medicamentos, água, alimentação e identificação atualizada."
            },

            {
                tipo: "titulo",

                texto:
                    "Preparação pode fazer diferença"
            },

            {
                tipo: "paragrafo",

                texto:
                    "Ter um plano de emergência permite uma saída mais segura e reduz o risco de abandono. O planejamento deve considerar o tamanho do animal, sua saúde, sua forma de transporte e locais que possam recebê-lo."
            }

        ]

    },


    {
        id: "telemedicina-gatos",

        titulo:
            "Telemedicina pode ajudar no cuidado de gatos com doenças crônicas",

        categoria: "saude",

        categoriasSecundarias: [
            "gatos",
            "ciencia"
        ],

        categoriaNome: "Saúde",

        categoriaIcone:
            "bi bi-heart-pulse-fill",

        data:
            "3 de junho de 2025",

        dataISO:
            "2025-06-03",

        tempoLeitura:
            "5 minutos",

        fonte:
            "UC Davis e Frontiers in Veterinary Science",

        fonteUrl:
            "https://www.sciencedaily.com/releases/2025/06/250603172914.htm",

        imagem:
            "https://images.unsplash.com/photo-1533738363-b7f9aef128ce?auto=format&fit=crop&w=1500&q=85",

        imagemAlt:
            "Gato descansando em casa",

        destaque: false,

        resumo:
            "Pesquisadores observaram que consultas veterinárias por vídeo podem orientar tutores e reduzir o estresse de gatos com problemas crônicos de mobilidade.",

        citacao:
            "A consulta remota não substitui todos os atendimentos presenciais, mas pode complementar o acompanhamento veterinário.",

        tags: [
            "Saúde felina",
            "Telemedicina",
            "Gatos",
            "Veterinária"
        ],

        conteudo: [

            {
                tipo: "paragrafo",

                texto:
                    "Levar um gato até uma clínica veterinária pode ser uma experiência estressante para o animal e para seu tutor. Caixa de transporte, carro, cheiros desconhecidos e presença de outros animais podem aumentar o desconforto."
            },

            {
                tipo: "paragrafo",

                texto:
                    "Pesquisadores da Universidade da Califórnia, em Davis, analisaram o uso de consultas por vídeo como apoio ao acompanhamento de gatos com problemas crônicos de mobilidade."
            },

            {
                tipo: "titulo",

                texto:
                    "Observação dentro da própria casa"
            },

            {
                tipo: "paragrafo",

                texto:
                    "Durante uma consulta por vídeo, o profissional pode observar como o gato anda, sobe em móveis, utiliza a caixa de areia e acessa potes de água e alimento dentro do ambiente onde vive."
            },

            {
                tipo: "citacao",

                texto:
                    "Observar a rotina real do animal pode ajudar na indicação de adaptações simples para melhorar seu conforto."
            },

            {
                tipo: "paragrafo",

                texto:
                    "Entre as possíveis orientações estão a instalação de rampas, alteração da altura dos potes, uso de superfícies antiderrapantes e mudanças na caixa de areia."
            },

            {
                tipo: "titulo",

                texto:
                    "Complemento, e não substituição"
            },

            {
                tipo: "paragrafo",

                texto:
                    "A telemedicina não elimina a necessidade de exames físicos, diagnósticos e atendimentos presenciais. Ela pode funcionar como ferramenta complementar em casos definidos pelo médico-veterinário."
            },

            {
                tipo: "paragrafo",

                texto:
                    "O acompanhamento de qualquer doença crônica deve ser feito por um profissional habilitado. Mudanças de medicamentos, alimentação ou tratamento nunca devem ser realizadas apenas com informações encontradas na internet."
            }

        ]

    },


    {
        id: "caes-julgam-pessoas",

        titulo:
            "Estudo investiga se cachorros conseguem reconhecer pessoas gentis",

        categoria: "ciencia",

        categoriasSecundarias: [
            "cachorros"
        ],

        categoriaNome: "Ciência",

        categoriaIcone:
            "bi bi-lightbulb-fill",

        data:
            "18 de julho de 2025",

        dataISO:
            "2025-07-18",

        tempoLeitura:
            "5 minutos",

        fonte:
            "Universidade de Kyoto",

        fonteUrl:
            "https://www.sciencedaily.com/releases/2025/07/250718031215.htm",

        imagem:
            "https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&fit=crop&w=1500&q=85",

        imagemAlt:
            "Cachorro observando uma pessoa",

        destaque: false,

        resumo:
            "Uma pesquisa colocou à prova a ideia popular de que cães identificam facilmente quem é uma pessoa boa ou má.",

        citacao:
            "Os resultados sugerem que a forma como os cães avaliam comportamentos humanos pode ser mais complexa do que imaginamos.",

        tags: [
            "Comportamento",
            "Cachorros",
            "Ciência",
            "Pesquisa"
        ],

        conteudo: [

            {
                tipo: "paragrafo",

                texto:
                    "Muitos tutores acreditam que seus cachorros conseguem reconhecer imediatamente quando uma pessoa é confiável. Uma pesquisa da Universidade de Kyoto decidiu analisar essa crença em situações controladas."
            },

            {
                tipo: "paragrafo",

                texto:
                    "Durante os experimentos, os cães observaram pessoas apresentando comportamentos diferentes em interações com outros animais. Depois, puderam escolher de qual pessoa se aproximariam."
            },

            {
                tipo: "titulo",

                texto:
                    "O resultado surpreendeu os pesquisadores"
            },

            {
                tipo: "paragrafo",

                texto:
                    "Os cães não demonstraram de forma consistente uma preferência pela pessoa que havia apresentado o comportamento considerado mais gentil."
            },

            {
                tipo: "citacao",

                texto:
                    "A ausência de uma escolha clara não significa que os cães não percebam emoções ou sinais sociais."
            },

            {
                tipo: "paragrafo",

                texto:
                    "Os pesquisadores explicam que a avaliação social dos cães pode depender do contexto, da experiência anterior do animal e da forma como cada experimento é realizado."
            },

            {
                tipo: "titulo",

                texto:
                    "Cães continuam sendo excelentes observadores"
            },

            {
                tipo: "paragrafo",

                texto:
                    "Cachorros conseguem perceber tom de voz, postura corporal, rotina e expressões. Entretanto, transformar essas percepções em um julgamento moral semelhante ao humano é uma capacidade muito mais complexa."
            },

            {
                tipo: "paragrafo",

                texto:
                    "Novos estudos poderão ajudar a compreender como idade, socialização, treinamento e convivência influenciam essas respostas."
            }

        ]

    },


    {
        id: "caes-impacto-vida-selvagem",

        titulo:
            "Pesquisa analisa os impactos de cães domésticos sobre animais silvestres",

        categoria: "ciencia",

        categoriasSecundarias: [
            "cachorros"
        ],

        categoriaNome: "Ciência",

        categoriaIcone:
            "bi bi-lightbulb-fill",

        data:
            "10 de abril de 2025",

        dataISO:
            "2025-04-10",

        tempoLeitura:
            "6 minutos",

        fonte:
            "Curtin University",

        fonteUrl:
            "https://www.sciencedaily.com/releases/2025/04/250409114840.htm",

        imagem:
            "https://images.unsplash.com/photo-1530281700549-e82e7bf110d6?auto=format&fit=crop&w=1500&q=85",

        imagemAlt:
            "Cachorro caminhando ao ar livre",

        destaque: false,

        resumo:
            "Pesquisadores chamaram atenção para os efeitos que cães sem controle podem causar em aves, pequenos animais e ecossistemas naturais.",

        citacao:
            "Passeios responsáveis protegem tanto o cachorro quanto a fauna existente nos locais visitados.",

        tags: [
            "Meio ambiente",
            "Cachorros",
            "Fauna",
            "Passeio responsável"
        ],

        conteudo: [

            {
                tipo: "paragrafo",

                texto:
                    "Cães domésticos fazem parte da vida de milhões de famílias, mas a presença deles em áreas naturais também precisa ser acompanhada com responsabilidade."
            },

            {
                tipo: "paragrafo",

                texto:
                    "Uma análise conduzida por pesquisadores da Curtin University reuniu evidências sobre perseguições, ataques, perturbações e outros efeitos relacionados à presença de cães em ambientes ocupados por animais silvestres."
            },

            {
                tipo: "titulo",

                texto:
                    "Mesmo cães na guia podem alterar o ambiente"
            },

            {
                tipo: "paragrafo",

                texto:
                    "Segundo a pesquisa, cheiros, urina, fezes, latidos e aproximações podem modificar o comportamento de espécies silvestres mesmo depois que o cachorro deixa o local."
            },

            {
                tipo: "citacao",

                texto:
                    "A solução não é impedir os passeios, mas realizá-los de forma segura, consciente e compatível com cada ambiente."
            },

            {
                tipo: "paragrafo",

                texto:
                    "Em regiões de proteção, praias com ninhos, trilhas e parques naturais, os tutores devem seguir as regras locais e evitar que os cães corram livremente."
            },

            {
                tipo: "titulo",

                texto:
                    "Como fazer um passeio responsável"
            },

            {
                tipo: "paragrafo",

                texto:
                    "Manter o cachorro na guia, recolher as fezes, respeitar áreas proibidas, evitar aproximação de animais silvestres e escolher locais apropriados são atitudes importantes."
            },

            {
                tipo: "paragrafo",

                texto:
                    "Essas medidas também protegem o próprio cão contra acidentes, fugas, confrontos, parasitas e contato com plantas ou substâncias perigosas."
            }

        ]

    },


    {
        id: "longevidade-gatos-cachorros",

        titulo:
            "Estudo busca explicar por que gatos geralmente vivem mais que cachorros",

        categoria: "ciencia",

        categoriasSecundarias: [
            "gatos",
            "cachorros",
            "saude"
        ],

        categoriaNome: "Ciência",

        categoriaIcone:
            "bi bi-lightbulb-fill",

        data:
            "8 de maio de 2025",

        dataISO:
            "2025-05-08",

        tempoLeitura:
            "5 minutos",

        fonte:
            "University of Bath",

        fonteUrl:
            "https://www.sciencedaily.com/releases/2025/05/250508112728.htm",

        imagem:
            "https://images.unsplash.com/photo-1529778873920-4da4926a72c2?auto=format&fit=crop&w=1500&q=85",

        imagemAlt:
            "Gato descansando",

        destaque: false,

        resumo:
            "Uma comparação entre dezenas de espécies de mamíferos encontrou relações entre longevidade, tamanho cerebral e sistemas de defesa do organismo.",

        citacao:
            "A longevidade depende de uma combinação de genética, espécie, porte, ambiente, prevenção e cuidados de saúde.",

        tags: [
            "Longevidade",
            "Gatos",
            "Cachorros",
            "Genética"
        ],

        conteudo: [

            {
                tipo: "paragrafo",

                texto:
                    "Por que gatos costumam alcançar idades mais avançadas que muitos cachorros? Uma pesquisa internacional analisou diferenças genéticas e evolutivas entre diversas espécies de mamíferos."
            },

            {
                tipo: "paragrafo",

                texto:
                    "Os pesquisadores compararam informações genéticas e o potencial máximo de vida de 46 espécies, buscando identificar padrões relacionados ao sistema imunológico e ao tamanho do cérebro."
            },

            {
                tipo: "titulo",

                texto:
                    "Não existe uma única explicação"
            },

            {
                tipo: "paragrafo",

                texto:
                    "Os resultados sugerem que espécies com maior longevidade podem apresentar investimentos evolutivos diferentes em mecanismos imunológicos. Entretanto, isso não significa que apenas um gene determine quanto um animal viverá."
            },

            {
                tipo: "citacao",

                texto:
                    "A duração da vida é influenciada por muitos fatores e não pode ser prevista apenas pela espécie do animal."
            },

            {
                tipo: "paragrafo",

                texto:
                    "Entre cães, por exemplo, porte, raça, alimentação, doenças hereditárias e qualidade dos cuidados podem causar diferenças importantes."
            },

            {
                tipo: "titulo",

                texto:
                    "Prevenção continua sendo fundamental"
            },

            {
                tipo: "paragrafo",

                texto:
                    "Consultas veterinárias, vacinação, controle de parasitas, alimentação equilibrada, exercícios e observação de mudanças comportamentais contribuem para a saúde ao longo da vida."
            },

            {
                tipo: "paragrafo",

                texto:
                    "Mesmo animais idosos podem ter boa qualidade de vida quando recebem acompanhamento adequado e adaptações compatíveis com suas necessidades."
            }

        ]

    },


    {
        id: "caes-detectores-medicina",

        titulo:
            "Personalidade pode influenciar o desempenho de cães detectores",

        categoria: "ciencia",

        categoriasSecundarias: [
            "cachorros",
            "saude"
        ],

        categoriaNome: "Ciência",

        categoriaIcone:
            "bi bi-lightbulb-fill",

        data:
            "10 de abril de 2025",

        dataISO:
            "2025-04-10",

        tempoLeitura:
            "4 minutos",

        fonte:
            "PLOS e University of Bristol",

        fonteUrl:
            "https://www.sciencedaily.com/releases/2025/04/250409154619.htm",

        imagem:
            "https://images.unsplash.com/photo-1518717758536-85ae29035b6d?auto=format&fit=crop&w=1500&q=85",

        imagemAlt:
            "Cachorro atento durante treinamento",

        destaque: false,

        resumo:
            "Um estudo exploratório encontrou possíveis relações entre tendências comportamentais e resultados de cães treinados para detecção médica.",

        citacao:
            "Conhecer o perfil comportamental pode ajudar treinadores a adaptar métodos e melhorar o bem-estar dos cães.",

        tags: [
            "Cães detectores",
            "Treinamento",
            "Comportamento",
            "Medicina"
        ],

        conteudo: [

            {
                tipo: "paragrafo",

                texto:
                    "Cães possuem uma capacidade olfativa impressionante e podem ser treinados para reconhecer odores relacionados a diferentes condições médicas."
            },

            {
                tipo: "paragrafo",

                texto:
                    "Uma pesquisa exploratória analisou se características comportamentais descritas como mais otimistas ou pessimistas poderiam estar relacionadas ao desempenho desses animais."
            },

            {
                tipo: "titulo",

                texto:
                    "Perfis diferentes podem apresentar vantagens diferentes"
            },

            {
                tipo: "paragrafo",

                texto:
                    "Os resultados indicaram que cães avaliados como mais otimistas apresentaram melhor desempenho geral em algumas tarefas, enquanto outros perfis demonstraram maior especificidade na detecção."
            },

            {
                tipo: "citacao",

                texto:
                    "Os resultados são iniciais e não devem ser usados para rotular ou excluir animais de treinamentos."
            },

            {
                tipo: "paragrafo",

                texto:
                    "Como o estudo foi exploratório, novas pesquisas com grupos maiores serão necessárias para confirmar as relações observadas."
            },

            {
                tipo: "titulo",

                texto:
                    "Treinamento individualizado"
            },

            {
                tipo: "paragrafo",

                texto:
                    "Cada cão apresenta motivações, respostas e ritmos diferentes. Conhecer essas particularidades permite adaptar recompensas, períodos de trabalho e formas de aprendizado."
            },

            {
                tipo: "paragrafo",

                texto:
                    "Além do desempenho, programas responsáveis devem priorizar descanso, enriquecimento, saúde física e bem-estar emocional."
            }

        ]

    }

];


/* =========================================================
   VARIÁVEIS DE CONTROLE
========================================================= */

let categoriaAtual = "todos";

let termoPesquisa = "";

let quantidadeVisivel = 6;

const quantidadePorPagina = 3;


/* =========================================================
   FUNÇÕES AUXILIARES
========================================================= */

function normalizarTexto(texto) {

    return String(texto)
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .toLowerCase()
        .trim();

}


function noticiaPertenceCategoria(noticia, categoria) {

    if (categoria === "todos") {
        return true;
    }

    if (noticia.categoria === categoria) {
        return true;
    }

    return noticia.categoriasSecundarias?.includes(categoria);

}


function noticiaCombinaPesquisa(noticia, termo) {

    if (!termo) {
        return true;
    }

    const textoCompleto = normalizarTexto([

        noticia.titulo,
        noticia.resumo,
        noticia.categoriaNome,
        noticia.fonte,
        ...noticia.tags

    ].join(" "));

    return textoCompleto.includes(normalizarTexto(termo));

}


function obterNoticiasFiltradas() {

    return noticias
        .filter((noticia) => {

            return noticiaPertenceCategoria(
                noticia,
                categoriaAtual
            );

        })
        .filter((noticia) => {

            return noticiaCombinaPesquisa(
                noticia,
                termoPesquisa
            );

        })
        .sort((a, b) => {

            return new Date(b.dataISO) - new Date(a.dataISO);

        });

}


function criarClasseCategoria(noticia) {

    return noticia.categoria || "";

}


function escaparHTML(texto) {

    const elemento = document.createElement("div");

    elemento.textContent = texto;

    return elemento.innerHTML;

}


/* =========================================================
   CRIAÇÃO DO CARD
========================================================= */

function criarCardNoticia(noticia, indice = 0) {

    const artigo = document.createElement("article");

    artigo.className = "noticia-card";

    artigo.style.animationDelay =
        `${Math.min(indice * 0.08, 0.4)}s`;


    artigo.innerHTML = `

        <a
            href="noticia.html?id=${encodeURIComponent(noticia.id)}"
            class="card-imagem"
            aria-label="Ler: ${escaparHTML(noticia.titulo)}"
        >

            <img
                src="${noticia.imagem}"
                alt="${escaparHTML(noticia.imagemAlt)}"
                loading="lazy"
            >

            <div class="card-imagem-overlay"></div>

            <span class="card-data-imagem">

                <i class="bi bi-calendar3"></i>

                ${noticia.data}

            </span>

        </a>


        <div class="card-conteudo">

            <div class="card-etiquetas">

                <span
                    class="
                        categoria-noticia
                        ${criarClasseCategoria(noticia)}
                    "
                >

                    <i class="${noticia.categoriaIcone}"></i>

                    ${noticia.categoriaNome}

                </span>

            </div>


            <h3>

                <a
                    href="noticia.html?id=${encodeURIComponent(noticia.id)}"
                    style="color: inherit;"
                >
                    ${escaparHTML(noticia.titulo)}
                </a>

            </h3>


            <p>
                ${escaparHTML(noticia.resumo)}
            </p>


            <div class="card-rodape">

                <span class="card-fonte">

                    <i class="bi bi-patch-check"></i>

                    <span>
                        ${escaparHTML(noticia.fonte)}
                    </span>

                </span>


                <a
                    href="noticia.html?id=${encodeURIComponent(noticia.id)}"
                    class="card-ler-mais"
                >

                    Ler matéria

                    <i class="bi bi-arrow-right"></i>

                </a>

            </div>

        </div>

    `;

    return artigo;

}


/* =========================================================
   NOTÍCIA EM DESTAQUE
========================================================= */

function renderizarDestaque() {

    const container =
        document.getElementById("noticiaDestaque");

    if (!container) {
        return;
    }


    const noticia =
        noticias.find((item) => item.destaque)
        || noticias[0];


    container.innerHTML = `

        <article class="noticia-destaque">

            <a
                href="noticia.html?id=${encodeURIComponent(noticia.id)}"
                class="destaque-imagem"
            >

                <img
                    src="${noticia.imagem}"
                    alt="${escaparHTML(noticia.imagemAlt)}"
                >

            </a>


            <div class="destaque-conteudo">

                <div class="destaque-etiquetas">

                    <span
                        class="
                            categoria-noticia
                            ${criarClasseCategoria(noticia)}
                        "
                    >

                        <i class="${noticia.categoriaIcone}"></i>

                        ${noticia.categoriaNome}

                    </span>


                    <span class="selo-destaque">

                        <i class="bi bi-stars"></i>

                        Destaque

                    </span>

                </div>


                <h2>
                    ${escaparHTML(noticia.titulo)}
                </h2>


                <p>
                    ${escaparHTML(noticia.resumo)}
                </p>


                <div class="meta-noticia">

                    <span>

                        <i class="bi bi-calendar3"></i>

                        ${noticia.data}

                    </span>


                    <span>

                        <i class="bi bi-clock"></i>

                        ${noticia.tempoLeitura}

                    </span>


                    <span>

                        <i class="bi bi-patch-check"></i>

                        ${escaparHTML(noticia.fonte)}

                    </span>

                </div>


                <a
                    href="noticia.html?id=${encodeURIComponent(noticia.id)}"
                    class="btn-ler-destaque"
                >

                    Ler matéria completa

                    <i class="bi bi-arrow-right"></i>

                </a>

            </div>

        </article>

    `;

}


/* =========================================================
   GRID PRINCIPAL
========================================================= */

function renderizarNoticias() {

    const grid =
        document.getElementById("noticiasGrid");

    if (!grid) {
        return;
    }


    const filtradas =
        obterNoticiasFiltradas();


    const visiveis =
        filtradas.slice(0, quantidadeVisivel);


    grid.innerHTML = "";


    visiveis.forEach((noticia, indice) => {

        grid.appendChild(
            criarCardNoticia(noticia, indice)
        );

    });


    atualizarContador(
        visiveis.length,
        filtradas.length
    );


    atualizarBotaoCarregarMais(
        visiveis.length,
        filtradas.length
    );


    atualizarMensagemVazia(
        filtradas.length
    );

}


function atualizarContador(visiveis, total) {

    const contador =
        document.getElementById("contadorNoticias");

    if (!contador) {
        return;
    }


    if (total === 0) {

        contador.textContent =
            "Nenhuma notícia encontrada";

        return;

    }


    if (visiveis === total) {

        contador.textContent =
            `${total} ${total === 1 ? "notícia encontrada" : "notícias encontradas"}`;

        return;

    }


    contador.textContent =
        `Mostrando ${visiveis} de ${total} notícias`;

}


function atualizarBotaoCarregarMais(visiveis, total) {

    const botao =
        document.getElementById("carregarMais");

    if (!botao) {
        return;
    }


    botao.hidden =
        total === 0
        || visiveis >= total;

}


function atualizarMensagemVazia(total) {

    const mensagem =
        document.getElementById("nenhumResultado");

    const grid =
        document.getElementById("noticiasGrid");

    if (!mensagem || !grid) {
        return;
    }


    const vazio = total === 0;

    mensagem.hidden = !vazio;

    grid.hidden = vazio;

}


/* =========================================================
   FILTROS
========================================================= */

function configurarFiltros() {

    const botoes =
        document.querySelectorAll(".filtro-btn");


    botoes.forEach((botao) => {

        botao.addEventListener("click", () => {

            categoriaAtual =
                botao.dataset.category || "todos";

            quantidadeVisivel = 6;


            botoes.forEach((item) => {

                item.classList.remove("ativo");

            });


            botao.classList.add("ativo");


            renderizarNoticias();

        });

    });

}


/* =========================================================
   PESQUISA
========================================================= */

function configurarPesquisa() {

    const input =
        document.getElementById("pesquisaNoticias");

    const limpar =
        document.getElementById("limparPesquisa");

    const resetar =
        document.getElementById("resetarFiltros");


    if (input) {

        input.addEventListener("input", () => {

            termoPesquisa = input.value;

            quantidadeVisivel = 6;

            renderizarNoticias();

        });

    }


    if (limpar && input) {

        limpar.addEventListener("click", () => {

            input.value = "";

            termoPesquisa = "";

            input.focus();

            renderizarNoticias();

        });

    }


    if (resetar) {

        resetar.addEventListener("click", () => {

            categoriaAtual = "todos";

            termoPesquisa = "";

            quantidadeVisivel = 6;


            if (input) {
                input.value = "";
            }


            document
                .querySelectorAll(".filtro-btn")
                .forEach((botao) => {

                    botao.classList.toggle(
                        "ativo",
                        botao.dataset.category === "todos"
                    );

                });


            renderizarNoticias();

        });

    }

}


/* =========================================================
   CARREGAR MAIS
========================================================= */

function configurarCarregarMais() {

    const botao =
        document.getElementById("carregarMais");

    if (!botao) {
        return;
    }


    botao.addEventListener("click", () => {

        quantidadeVisivel += quantidadePorPagina;

        renderizarNoticias();

    });

}


/* =========================================================
   NEWSLETTER
========================================================= */

function configurarNewsletter() {

    const formulario =
        document.getElementById("newsletterForm");

    const mensagem =
        document.getElementById("newsletterMensagem");


    if (!formulario || !mensagem) {
        return;
    }


    formulario.addEventListener("submit", (evento) => {

        evento.preventDefault();


        const input =
            document.getElementById("newsletterEmail");

        const email =
            input?.value.trim();


        if (!email) {

            mensagem.textContent =
                "Digite um e-mail válido.";

            return;

        }


        mensagem.textContent =
            "Cadastro realizado! Em breve você receberá as novidades da Garra & Patas. 🐾";


        formulario.reset();


        window.setTimeout(() => {

            mensagem.textContent = "";

        }, 6000);

    });

}


/* =========================================================
   PÁGINA INDIVIDUAL
========================================================= */

function obterNoticiaPelaURL() {

    const parametros =
        new URLSearchParams(window.location.search);

    const id =
        parametros.get("id");

    return noticias.find(
        (noticia) => noticia.id === id
    );

}


function criarConteudoArtigo(noticia) {

    return noticia.conteudo
        .map((bloco) => {

            if (bloco.tipo === "titulo") {

                return `
                    <h2>
                        ${escaparHTML(bloco.texto)}
                    </h2>
                `;

            }


            if (bloco.tipo === "citacao") {

                return `
                    <blockquote>
                        ${escaparHTML(bloco.texto)}
                    </blockquote>
                `;

            }


            return `
                <p>
                    ${escaparHTML(bloco.texto)}
                </p>
            `;

        })
        .join("");

}


function criarTags(noticia) {

    return noticia.tags
        .map((tag) => {

            return `
                <span class="tag-artigo">
                    ${escaparHTML(tag)}
                </span>
            `;

        })
        .join("");

}


function criarLinkWhatsApp(noticia) {

    const texto =
        `${noticia.titulo} - ${window.location.href}`;

    return (
        "https://wa.me/?text="
        + encodeURIComponent(texto)
    );

}


function criarLinkFacebook() {

    return (
        "https://www.facebook.com/sharer/sharer.php?u="
        + encodeURIComponent(window.location.href)
    );

}


function renderizarPaginaNoticia() {

    const pagina =
        document.getElementById("paginaNoticia");

    if (!pagina) {
        return;
    }


    const noticia =
        obterNoticiaPelaURL();


    if (!noticia) {

        pagina.innerHTML = `

            <section class="noticia-nao-encontrada">

                <i class="fa-solid fa-paw"></i>

                <h1>
                    Matéria não encontrada
                </h1>

                <p>
                    O endereço acessado não corresponde a uma
                    notícia disponível.
                </p>

                <a
                    href="blog.html"
                    class="btn-ler-destaque"
                    style="margin: 20px auto 0;"
                >

                    Voltar para o blog

                    <i class="bi bi-arrow-left"></i>

                </a>

            </section>

        `;

        return;

    }


    document.title =
        `${noticia.titulo} | Garra & Patas`;


    pagina.innerHTML = `

        <nav
            class="breadcrumb-blog"
            aria-label="Navegação estrutural"
        >

            <a href="menu.html">
                Início
            </a>

            <i class="bi bi-chevron-right"></i>

            <a href="blog.html">
                Blog
            </a>

            <i class="bi bi-chevron-right"></i>

            <span>
                ${noticia.categoriaNome}
            </span>

        </nav>


        <article class="artigo-blog">

            <header class="artigo-cabecalho">

                <span
                    class="
                        categoria-noticia
                        artigo-categoria
                        ${criarClasseCategoria(noticia)}
                    "
                >

                    <i class="${noticia.categoriaIcone}"></i>

                    ${noticia.categoriaNome}

                </span>


                <h1>
                    ${escaparHTML(noticia.titulo)}
                </h1>


                <p class="artigo-resumo">
                    ${escaparHTML(noticia.resumo)}
                </p>


                <div class="artigo-meta">

                    <span>

                        <i class="bi bi-calendar3"></i>

                        ${noticia.data}

                    </span>


                    <span>

                        <i class="bi bi-clock"></i>

                        ${noticia.tempoLeitura}

                    </span>


                    <span>

                        <i class="bi bi-patch-check"></i>

                        Fonte: ${escaparHTML(noticia.fonte)}

                    </span>

                </div>

            </header>


            <div class="artigo-imagem-principal">

                <img
                    src="${noticia.imagem}"
                    alt="${escaparHTML(noticia.imagemAlt)}"
                >

            </div>


            <div class="artigo-layout">

                <aside class="compartilhar-lateral">

                    <span>
                        Compartilhar
                    </span>


                    <a
                        href="${criarLinkWhatsApp(noticia)}"
                        target="_blank"
                        rel="noopener noreferrer"
                        aria-label="Compartilhar no WhatsApp"
                        title="Compartilhar no WhatsApp"
                    >
                        <i class="bi bi-whatsapp"></i>
                    </a>


                    <a
                        href="${criarLinkFacebook()}"
                        target="_blank"
                        rel="noopener noreferrer"
                        aria-label="Compartilhar no Facebook"
                        title="Compartilhar no Facebook"
                    >
                        <i class="bi bi-facebook"></i>
                    </a>


                    <button
                        type="button"
                        id="copiarLinkNoticia"
                        aria-label="Copiar link"
                        title="Copiar link"
                    >
                        <i class="bi bi-link-45deg"></i>
                    </button>

                </aside>


                <div class="artigo-conteudo">

                    ${criarConteudoArtigo(noticia)}


                    <div class="aviso-editorial">

                        <i class="bi bi-info-circle-fill"></i>

                        <p>
                            Esta matéria apresenta um resumo informativo
                            produzido para o Blog Garra & Patas com base
                            na fonte indicada. Conteúdos de saúde não
                            substituem uma consulta com médico-veterinário.
                        </p>

                    </div>


                    <div class="fonte-original">

                        <div>

                            <span>
                                Fonte original
                            </span>

                            <strong>
                                ${escaparHTML(noticia.fonte)}
                            </strong>

                        </div>


                        <a
                            href="${noticia.fonteUrl}"
                            target="_blank"
                            rel="noopener noreferrer"
                        >

                            Acessar fonte

                            <i class="bi bi-box-arrow-up-right"></i>

                        </a>

                    </div>


                    <div class="tags-artigo">

                        <span>
                            Tags:
                        </span>

                        ${criarTags(noticia)}

                    </div>


                    <div class="voltar-blog">

                        <a href="blog.html">

                            <i class="bi bi-arrow-left"></i>

                            Voltar para todas as notícias

                        </a>

                    </div>

                </div>

            </div>

        </article>


        <section class="noticias-relacionadas">

            <div class="relacionadas-cabecalho">

                <span>
                    Continue lendo
                </span>

                <h2>
                    Notícias relacionadas
                </h2>

            </div>


            <div
                class="noticias-grid"
                id="noticiasRelacionadas"
            ></div>

        </section>

    `;


    configurarCopiarLink();

    renderizarRelacionadas(noticia);

}


/* =========================================================
   NOTÍCIAS RELACIONADAS
========================================================= */

function calcularPontuacaoRelacionada(
    noticiaAtual,
    candidata
) {

    let pontos = 0;


    if (
        candidata.categoria
        === noticiaAtual.categoria
    ) {
        pontos += 5;
    }


    candidata.categoriasSecundarias
        ?.forEach((categoria) => {

            if (
                noticiaAtual.categoriasSecundarias
                    ?.includes(categoria)
            ) {
                pontos += 2;
            }


            if (
                categoria
                === noticiaAtual.categoria
            ) {
                pontos += 2;
            }

        });


    candidata.tags.forEach((tag) => {

        if (noticiaAtual.tags.includes(tag)) {
            pontos += 1;
        }

    });


    return pontos;

}


function obterNoticiasRelacionadas(noticiaAtual) {

    return noticias
        .filter((noticia) => {

            return noticia.id !== noticiaAtual.id;

        })
        .map((noticia) => {

            return {

                noticia,

                pontuacao:
                    calcularPontuacaoRelacionada(
                        noticiaAtual,
                        noticia
                    )

            };

        })
        .sort((a, b) => {

            if (b.pontuacao !== a.pontuacao) {

                return b.pontuacao - a.pontuacao;

            }

            return (
                new Date(b.noticia.dataISO)
                - new Date(a.noticia.dataISO)
            );

        })
        .slice(0, 3)
        .map((item) => item.noticia);

}


function renderizarRelacionadas(noticiaAtual) {

    const container =
        document.getElementById(
            "noticiasRelacionadas"
        );

    if (!container) {
        return;
    }


    const relacionadas =
        obterNoticiasRelacionadas(noticiaAtual);


    container.innerHTML = "";


    relacionadas.forEach((noticia, indice) => {

        container.appendChild(
            criarCardNoticia(noticia, indice)
        );

    });

}


/* =========================================================
   COMPARTILHAMENTO
========================================================= */

function configurarCopiarLink() {

    const botao =
        document.getElementById(
            "copiarLinkNoticia"
        );

    if (!botao) {
        return;
    }


    botao.addEventListener("click", async () => {

        try {

            await navigator.clipboard.writeText(
                window.location.href
            );


            const icone =
                botao.querySelector("i");


            icone.className =
                "bi bi-check-lg";


            botao.title =
                "Link copiado";


            window.setTimeout(() => {

                icone.className =
                    "bi bi-link-45deg";

                botao.title =
                    "Copiar link";

            }, 2200);

        }
        catch (erro) {

            window.prompt(
                "Copie o endereço da notícia:",
                window.location.href
            );

        }

    });

}


/* =========================================================
   TRATAMENTO DE ERRO DE IMAGENS
========================================================= */

function configurarErrosDeImagem() {

    document.addEventListener(
        "error",
        (evento) => {

            const elemento =
                evento.target;


            if (
                elemento.tagName !== "IMG"
                || elemento.dataset.fallbackAplicado
            ) {
                return;
            }


            elemento.dataset.fallbackAplicado =
                "true";


            elemento.src =
                "https://images.unsplash.com/photo-1450778869180-41d0601e046e?auto=format&fit=crop&w=1400&q=80";

        },
        true
    );

}


/* =========================================================
   INICIALIZAÇÃO
========================================================= */

function iniciarBlog() {

    configurarErrosDeImagem();

    renderizarDestaque();

    renderizarNoticias();

    configurarFiltros();

    configurarPesquisa();

    configurarCarregarMais();

    configurarNewsletter();

    renderizarPaginaNoticia();

}


document.addEventListener(
    "DOMContentLoaded",
    iniciarBlog
);
