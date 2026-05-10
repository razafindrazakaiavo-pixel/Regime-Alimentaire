<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\SanteModel;
use App\Models\ObjectifModel;
use App\Models\RegimeModel;
use App\Models\RegimeSportModel;
use App\Models\UserObjectifModel;
use App\Models\CodePortefeuilleModel;
use App\Models\RechargePortefeuilleModel;
use App\Models\AbonnementGoldModel;
use App\Models\UserRegimeModel;

class UserController extends BaseController
{
    public function login()
    {
        return view('user/login');
    }

    public function register()
    {
        return view('user/register_user');
    }

    public function ViewSante()
    {
        return view('user/register_sante');
    }

    public function saveUser()
    {
        $model = new UserModel();

        $data = [
            'nom' => $this->request->getPost('nom'),
            'email' => $this->request->getPost('email'),
            'mot_de_passe' => (string) $this->request->getPost('mot_de_passe'),
            'date_de_naissance' => $this->request->getPost('date_naissance'),
            'genre' => $this->request->getPost('genre')
        ];

        $model->insert($data);
        $user_id = $model->getInsertID();

        session()->set('temp_user_id', $user_id);

        return redirect()->to('/register-sante');
    }

    public function saveSante()
    {
        $tempUserId = session()->get('temp_user_id');
        if (!$tempUserId) {
            return redirect()->to('/register')->with('Error', "Session expirée. Merci de vous réinscrire.");
        }

        $model = new SanteModel();

        $poids = (float) $this->request->getPost('poids');
        $taille = (float) $this->request->getPost('taille');

        if ($poids && $taille) {
            $tailleM = $taille / 100;
            $imc = $poids / ($tailleM * $tailleM);
            $imc = round($imc, 1);

            $data = [
                'user_id' => (int) $tempUserId,
                'taille'  => $taille,
                'poids'   => $poids,
                'imc'     => $imc
            ];

            $model->insert($data);
        } else {
            return redirect()->back()->with('error', 'Veuillez remplir taille et poids');
        }

        session()->set('user_id', (int) $tempUserId);
        session()->remove('temp_user_id');

        return redirect()->to('/Accueil');
    }

    public function doLogin()
    {
        $model = new UserModel();

        $recuperation_email = $this->request->getPost('email');
        $recuperation_password = $this->request->getPost('mot_de_passe');
        $requetUser = $model->where('email', $recuperation_email)->first();

        if ($requetUser && $recuperation_password === $requetUser['mot_de_passe']) {
            session()->set('user_id', $requetUser['id']);
            return redirect()->to('/Accueil');
        }

        return redirect()->back()->with('Error', 'Email ou mot de passe incorrect');
    }

    public function profil()
    {
        $userId = session()->get('user_id');
        if (!$userId) {
            return redirect()->to('/');
        }
        $userModel = new UserModel();
        $santeModel = new SanteModel();

        $user = $userModel->find($userId);
        $sante = $santeModel->where('user_id', $userId)->orderBy('id', 'DESC')->first();

        $imc = null;
        if ($sante) {
            $taille = (float) ($sante['taille'] ?? 0);
            $poids = (float) ($sante['poids'] ?? 0);

            if ($taille > 0) {
                $tailleM = $taille / 100;
                $imc = round($poids / ($tailleM * $tailleM), 1);
            }
        }

        return view('user/profil', [
            'user'  => $user,
            'sante' => $sante,
            'imc'   => $imc
        ]);
    }

    public function updateProfil()
    {
        $userId = session()->get('user_id');
        if (!$userId) {
            return redirect()->to('/');
        }

        $model = new SanteModel();
        $poids = (float) $this->request->getPost('poids');
        $taille = (float) $this->request->getPost('taille');

        if ($poids > 0 && $taille > 0) {
            $tailleM = $taille / 100;
            $imc = round($poids / ($tailleM * $tailleM), 1);

            $model->insert([
                'user_id' => (int) $userId,
                'taille'  => $taille,
                'poids'   => $poids,
                'imc'     => $imc
            ]);
            return redirect()->to('/profil')->with('success', 'Profil mis à jour');
        }

        return redirect()->back()->with('error', 'Veuillez remplir correctement les champs');
    }

    public function updateAccount()
    {
        $userId = session()->get('user_id');
        if (!$userId) {
            return redirect()->to('/');
        }

        $userModel = new UserModel();
        $user = $userModel->find($userId);
        if (!$user) {
            return redirect()->back()->with('error', 'Utilisateur non trouvé');
        }

        $nom = $this->request->getPost('nom');
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('mot_de_passe');
        $date_de_naissance = $this->request->getPost('date_de_naissance');

        $data = [];

        if ($nom) {
            $data['nom'] = $nom;
        }

        if ($email) {
            $data['email'] = $email;
        }

        if (!empty($password)) {
            $data['mot_de_passe'] = $password;
        }

        if($date_de_naissance)
        {
            $data['date_de_naissance'] = $date_de_naissance;        }

        
        if (empty($data)) {
            return redirect()->back()->with('error', 'Aucune donnée à mettre à jour');
        }

        $userModel->update($userId, $data);

        return redirect()->to('/profil')->with('success', 'Vos informations ont été mises à jour avec succès');
    }

    public function Accueil()
    {
        $userId = session()->get('user_id');
        if (!$userId) {
            return redirect()->to('/');
        }

        $userModel = new UserModel();
        $santeModel = new SanteModel();

        $user = $userModel->find($userId);
        $sante = $santeModel->where('user_id', $userId)->orderBy('id', 'DESC')->first();

        $taille = $sante['taille'] ?? null;
        $poids = $sante['poids'] ?? null;

        $imc = null;
        if (!empty($taille) && !empty($poids)) {
            $tailleM = ((float) $taille) / 100;
            if ($tailleM > 0) {
                $imc = (float) $poids / ($tailleM * $tailleM);
                $imc = round($imc, 1);
            }
        }

        return view('user/Accueil', [
            'user' => $user,
            'sante' => $sante,
            'imc' => $imc,
        ]);
    }

    public function getObjectif()
    {
        $model = new ObjectifModel();

        $data['objectifs'] = $model->findAll();
        return view('user/objectif', $data);
    }

    public function ObjectifChoisie($id)
    {
        $userId = session()->get('user_id');
        if (!$userId) return redirect()->to('/');

        $regimeModel = new RegimeModel();
        $objectifModel = new ObjectifModel();
        $regimeSportModel = new RegimeSportModel();
        $userObjModel = new UserObjectifModel();
        $userModel = new UserModel();
        $santeModel = new SanteModel();

        $userObjModel->insert([
            'user_id' => $userId,
            'objectif_id' => $id
        ]);

        $user = $userModel->find($userId);
        $sante = $santeModel->where('user_id', $userId)->orderBy('id', 'DESC')->first();

        $data['objectif'] = $objectifModel->find($id);
        $data['is_gold'] = (isset($user['is_gold']) && $user['is_gold'] == 1);

        if ($id == 1) {
            $query = $regimeModel->where('variation_poids >', 0);
        } else if ($id == 2) {
            $query = $regimeModel->where('variation_poids <', 0);
        } else if ($id == 3 && $sante) {
            $tailleM = $sante['taille'] / 100;
            $poidsIdeal = 22 * ($tailleM * $tailleM);
            $data['poids_ideal'] = round($poidsIdeal, 1);

            $condition = ($poidsIdeal > $sante['poids']) ? 'variation_poids >' : 'variation_poids <';
            $query = $regimeModel->where($condition, 0);
        } else {
            $query = $regimeModel;
        }

        $regimes = $query->findAll();

        foreach ($regimes as &$regime) {
            $regime['sports'] = $regimeSportModel
                ->select('activites_sportives.*')
                ->join('activites_sportives', 'activites_sportives.id = regime_sport.sport_id')
                ->where('regime_sport.regime_id', $regime['id'])
                ->findAll();
        }

        $data['regimes'] = $regimes;
        $data['Idobjectif'] = $id;

        return view('user/regime', $data);
    }

    public function viewPorteMonnaie()
    {
        $userId = session()->get('user_id');
        if (!$userId) return redirect()->to('/');

        $userModel = new UserModel();
        $data['user'] = $userModel->find($userId);

        return view('user/porte_money', $data);
    }

    public function rechargeWallet()
    {
        $userId = session()->get('user_id');
        if (!$userId) return redirect()->to('/');

        $codeStr = $this->request->getPost('code');
        $codeModel = new CodePortefeuilleModel();
        $userModel = new UserModel();
        $rechargeModel = new RechargePortefeuilleModel();

        $codeEntry = $codeModel->where('code', $codeStr)
                               ->where('utilise', 0)
                               ->first();

        if ($codeEntry) {
            $user = $userModel->find($userId);

            if (!$user) {
                return redirect()->back()->with('Error', 'Utilisateur non trouvé.');
            }

            $ancienSolde = is_array($user) ? ($user['porte_monnaie'] ?? 0) : ($user->porte_monnaie ?? 0);
            $nouveauSolde = (float)$ancienSolde + (float)$codeEntry['montant'];

            $db = $userModel->db();
            $db->transStart();

            $userModel->update($userId, ['porte_monnaie' => $nouveauSolde]);
            $codeModel->update($codeEntry['id'], ['utilise' => 1]);
            $rechargeModel->insert([
                'user_id'       => $userId,
                'code_id'       => $codeEntry['id'],
                'date_recharge' => date('Y-m-d H:i:s')
            ]);

            $db->transComplete();

            if ($db->transStatus() === FALSE) {
                return redirect()->back()->with('Error', 'Une erreur est survenue lors de la recharge. Veuillez réessayer.');
            }

            return redirect()->back()->with('success', 'Compte crédité de ' . number_format($codeEntry['montant'], 0, ',', ' ') . ' Ar');
        }

        return redirect()->back()->with('Error', 'Code invalide ou déjà utilisé.');
    }

    public function buyGold()
    {
        $userId = session()->get('user_id');
        if (!$userId) return redirect()->to('/');

        $userModel = new UserModel();
        $user = $userModel->find($userId);

        $goldPrice = 50000;

        if ($user['porte_monnaie'] < $goldPrice) {
            return redirect()->to('/Accueil')->with('Error', 'Solde insuffisant pour passer Gold.');
        }

        $userModel->update($userId, [
            'porte_monnaie' => $user['porte_monnaie'] - $goldPrice,
            'is_gold' => 1
        ]);

        return redirect()->to('/Accueil')->with('success', 'Félicitations ! Vous êtes maintenant membre Gold.');
    }

    public function souscrireRegime()
    {
        $userId = session()->get('user_id');
        if (!$userId) return redirect()->to('/');

        $regimeId = $this->request->getPost('regime_id');
        if (!$regimeId) return redirect()->back();

        $regimeModel = new RegimeModel();
        $userModel = new UserModel();

        $user = $userModel->find($userId);
        $regime = $regimeModel->find($regimeId);

        if (!$regime) return redirect()->back()->with('Error', 'Régime introuvable.');

        $prix = (float) $regime['prix'];
        if (isset($user['is_gold']) && $user['is_gold'] == 1) {
            $prix *= 0.85;
        }

        if ($user['porte_monnaie'] < $prix) {
            return redirect()->back()->with('Error', 'Solde insuffisant dans votre porte-monnaie.');
        }

        $userModel->update($userId, [
            'porte_monnaie' => $user['porte_monnaie'] - $prix
        ]);

        return redirect()->to('/Accueil')->with('success', 'Souscription réussie au programme : ' . $regime['nom']);
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/');
    }
}
