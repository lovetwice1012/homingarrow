<?php

    namespace nexuscore\task;


    use pocketmine\plugin\Plugin;
    use pocketmine\scheduler\Task;
    use pocketmine\Server;

    class broadcasttask extends Task
    {
        private $plugin;

        public function __construct(Plugin $plugin)
        {
            $this->plugin = $plugin;
        }

        public function onRun():void
        {
             $array = ["/bank コマンドで所持金を銀行に預けておくと利息がつきます！","ガチャは/gtコマンドで引くことができます。特殊効果付きの弓など、確定ガチャがあるものは確定ガチャの方を引くことで出費を減らせるかもしれません！","自分の土地は必ず土地保護しましょう！","現在東京駅完全再現プロジェクトを行なっています。参加希望の方はyus10124かkumakuma0713までお願いします。","Discordサーバーにはもう参加しましたか？ https://discord.gg/Wb5jU6ZBKS このリンクから参加できます！","Twitterをやっていますか？条件を満たしていればSNSパートナーになることができます！SNSパートナーになると継続的にSNSパートナーだけの限定報酬やサーバー内通貨がもらえます！","バグは必ず報告しましょう！報告が認められると報酬がもらえます！","荒らしを見つけたら証拠を取ってOPに提出してください。","シールド値は死亡時に更新されます。シールド値付きの装備を装備した場合は一度自殺しましょう。","/jobコマンドで職業につけます。職業につくとブロックの設置や破壊でお金をもらうことができます","ガチャを作って見ませんか？自分の好きなアイテムを好きな名前で排出させられます。エンチャントも自由です！詳しくはDiscordの「ユーザー作成ガチャ」を見てください！","現在JAVA版のユーザーがNexusに接続できるように開発を進めています。バグがあれば教えてください。","マルチプロトコルにベータ対応しました。マイクラを更新していなくてもサーバーに入れるようになっています。(一部対応していないバージョンがあります)"];
             $value = random_int(0, (count($array)-1));
             $players = Server::getInstance()->getOnlinePlayers();
             foreach ($players as $p){
                 $p->sendMessage("§e[お知らせ] ".$array[$value]);
                
             }
        }
    }