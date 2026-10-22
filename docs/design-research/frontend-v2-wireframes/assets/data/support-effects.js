/* ============================================================================
   TRAINER DESK v2.0.0 HFW — the support-effect dictionary.
   GENERATED FILE. Do not hand-edit; run `php build-data.php` to regenerate.
   
   The support-effect dictionary: the thirty-five effects a card can carry, each
   with the client's name, the source's unit word and the source's own
   description of what it does.

   Provenance: `database/database.sqlite` (`support_effects`).

   This file exists so the thirty-five rows are stated once. A card's effect
   row carries the `effectId` to join on, which is the shape the database holds
   and the shape a surface that prints a card's effects wants.

   `calc` is how the effect combines, and the source states it for four rows
   only: `mult` on the friendship bonus, the mood effect and one more, and
   `add` on the specialty priority. Where the source states no mode the field
   is null, because naming a fourth mode would state a rule no source holds.
   FROZEN 2026-10-23 · capture 2026-10-23T05:53:07+08:00 · source commit fa394329
   Seed identity (sha256, first 12 hex, of database/seeders/data/*.json):
     characters.c6676539.json 4f3523ea4301
     gametora-characters.e9e9ee6d.json e9e9ee6d5246
     race-tier-labels-2026-09-29.json 7ee30f0ee28e
     race_instances.json 294424fc78e0
     races.json 55dde7c90921
     skills.609afe88.json 609afe887a29
     support-cards.88dea522.json 88dea5221217
     support_effects.ca447e53.json ca447e53c376
     ura-objectives.ead4bbfe.json ead4bbfefb44
     ura-races.json c12e8867d550
   Ageing rule: see README.md in this directory.
   ============================================================================ */

(function (root, factory) {
    'use strict';
    var data = factory();
    if (typeof module === 'object' && module.exports) { module.exports = data; }
    root.TRAINER_DESK_DATA = root.TRAINER_DESK_DATA || {};
    root.TRAINER_DESK_DATA.supportEffects = data.supportEffects;
    root.TRAINER_DESK_DATA.supportEffectsSchema = data.supportEffectsSchema;
})(typeof globalThis !== 'undefined' ? globalThis : this, function () {
    'use strict';

    var SUPPORT_EFFECTS = [
    {"id":"effect-1","effectId":1,"name":"Friendship Bonus","nameJa":"友情ボーナス","calc":"mult","symbol":"percent","description":"Increases the effectiveness of Friendship Training"},
    {"id":"effect-2","effectId":2,"name":"Mood Effect","nameJa":"やる気効果アップ","calc":null,"symbol":"percent","description":"Amplifies the effect of mood when training together"},
    {"id":"effect-3","effectId":3,"name":"Speed Bonus","nameJa":"スピードボーナス","calc":null,"symbol":"none","description":"Increases Speed gain when training together"},
    {"id":"effect-4","effectId":4,"name":"Stamina Bonus","nameJa":"スタミナボーナス","calc":null,"symbol":"none","description":"Increases Stamina gain when training together"},
    {"id":"effect-5","effectId":5,"name":"Power Bonus","nameJa":"パワーボーナス","calc":null,"symbol":"none","description":"Increases Power gain when training together"},
    {"id":"effect-6","effectId":6,"name":"Guts Bonus","nameJa":"根性ボーナス","calc":null,"symbol":"none","description":"Increases Guts gain when training together"},
    {"id":"effect-7","effectId":7,"name":"Wit Bonus","nameJa":"賢さボーナス","calc":null,"symbol":"none","description":"Increases Wit gain when training together"},
    {"id":"effect-8","effectId":8,"name":"Training Effectiveness","nameJa":"トレーニング効果アップ","calc":null,"symbol":"percent","description":"Increases the effectiveness of training performed together"},
    {"id":"effect-9","effectId":9,"name":"Initial Speed","nameJa":"初期スピードアップ","calc":null,"symbol":"none","description":"Increases initial Speed when beginning a Career playthrough"},
    {"id":"effect-10","effectId":10,"name":"Initial Stamina","nameJa":"初期スタミナアップ","calc":null,"symbol":"none","description":"Increases initial Stamina when beginning a Career playthrough"},
    {"id":"effect-11","effectId":11,"name":"Initial Power","nameJa":"初期パワーアップ","calc":null,"symbol":"none","description":"Increases initial Power when beginning a Career playthrough"},
    {"id":"effect-12","effectId":12,"name":"Initial Guts","nameJa":"初期根性アップ","calc":null,"symbol":"none","description":"Increases initial Guts when beginning a Career playthrough"},
    {"id":"effect-13","effectId":13,"name":"Initial Wit","nameJa":"初期賢さアップ","calc":null,"symbol":"none","description":"Increases initial Wit when beginning a Career playthrough"},
    {"id":"effect-14","effectId":14,"name":"Initial Friendship Gauge","nameJa":"初期絆ゲージアップ","calc":null,"symbol":"none","description":"Increases initial Friendship Gauge when beginning a Career playthrough"},
    {"id":"effect-15","effectId":15,"name":"Race Bonus","nameJa":"レースボーナス","calc":null,"symbol":"percent","description":"Increases stat gain from races"},
    {"id":"effect-16","effectId":16,"name":"Fan Bonus","nameJa":"ファン数ボーナス","calc":null,"symbol":"percent","description":"Increases fan gain from races"},
    {"id":"effect-17","effectId":17,"name":"Hint Levels","nameJa":"ヒントLvアップ","calc":null,"symbol":"level","description":"Increases the level of hints gained through events"},
    {"id":"effect-18","effectId":18,"name":"Hint Frequency","nameJa":"ヒント発生率アップ","calc":null,"symbol":"percent","description":"Increases the frequency at which hint events occur"},
    {"id":"effect-19","effectId":19,"name":"Specialty Priority","nameJa":"得意率アップ","calc":"add","symbol":"none","description":"Increases the frequency at which the character participates in their preferred training type"},
    {"id":"effect-20","effectId":20,"name":"Max Speed","nameJa":"スピード限界値アップ","calc":null,"symbol":"none","description":"Increases max Speed value when beginning a Career playthrough"},
    {"id":"effect-21","effectId":21,"name":"Max Stamina","nameJa":"スタミナ限界値アップ","calc":null,"symbol":"none","description":"Increases max Stamina value when beginning a Career playthrough"},
    {"id":"effect-22","effectId":22,"name":"Max Power","nameJa":"パワー限界値アップ","calc":null,"symbol":"none","description":"Increases max Power value when beginning a Career playthrough"},
    {"id":"effect-23","effectId":23,"name":"Max Guts","nameJa":"根性限界値アップ","calc":null,"symbol":"none","description":"Increases max Guts value when beginning a Career playthrough"},
    {"id":"effect-24","effectId":24,"name":"Max Wit","nameJa":"賢さ限界値アップ","calc":null,"symbol":"none","description":"Increases max Wit value when beginning a Career playthrough"},
    {"id":"effect-25","effectId":25,"name":"Event Recovery","nameJa":"イベント回復量アップ","calc":null,"symbol":"percent","description":"Increases Energy recovery from this Support Card's events"},
    {"id":"effect-26","effectId":26,"name":"Event Effectiveness","nameJa":"イベント効果アップ","calc":null,"symbol":"percent","description":"Increases stat gain from this Support Card's events"},
    {"id":"effect-27","effectId":27,"name":"Failure Protection","nameJa":"失敗率ダウン","calc":"mult","symbol":"percent","description":"Decreases the probability of failure when training together"},
    {"id":"effect-28","effectId":28,"name":"Energy Cost Reduction","nameJa":"体力消費ダウン","calc":"mult","symbol":"percent","description":"Decreases Energy consumed when training together"},
    {"id":"effect-29","effectId":29,"name":"Minigame Effectiveness","nameJa":"ミニゲーム効果アップ","calc":null,"symbol":"percent","description":"Increases stat gain from minigames"},
    {"id":"effect-30","effectId":30,"name":"Skill Point Bonus","nameJa":"スキルPtボーナス","calc":null,"symbol":"none","description":"Increases skill point gain when training together"},
    {"id":"effect-31","effectId":31,"name":"Wit Friendship Recovery","nameJa":"賢さ友情回復量アップ","calc":null,"symbol":"none","description":"Increases Energy recovery from Wit Friendship Training"},
    {"id":"effect-32","effectId":32,"name":"Initial Skill Points Up","nameJa":"初期スキルPtアップ","calc":null,"symbol":"none","description":"Initial skill points at the start of training are increased"},
    {"id":"effect-33","effectId":33,"name":"Hint Quantity Bonus","nameJa":"ヒント獲得数アップ","calc":null,"symbol":null,"description":null},
    {"id":"effect-41","effectId":41,"name":"All Stats Bonus","nameJa":"全パラメータボーナス","calc":null,"symbol":null,"description":"All stats bonus"},
    {"id":"effect-9991","effectId":9991,"name":"Increases Stats from Hints","nameJa":"ヒントイベント効果アップ","calc":null,"symbol":"none","description":"Increases stats from hints"}
];

    var SUPPORT_EFFECTS_SCHEMA = {"entity":"Support effect","count":35,"source":"the catalogue's own `support_effects` table","fields":{"id":"the key that never changes, `effect-` and the source id","effectId":"the source's effect id; the join key for a card's effect row","name":"the client name of the effect","nameJa":"the Japanese name","calc":"how the effect combines: `mult`, `add`, or null where the source states no mode","symbol":"the source's unit word: percent, none, level, or null","description":"the source's own description of the effect, kept verbatim"}};

    return { supportEffects: SUPPORT_EFFECTS, supportEffectsSchema: SUPPORT_EFFECTS_SCHEMA };
});
