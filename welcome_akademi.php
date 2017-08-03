<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Welcome extends CI_Controller {
	 public $baglimi = true;
	 ///// ilk user kurulumu unutma ;
	public function index()
	{
		$this->session_control();
		
		$user_id = $this->session->userdata('user_id');		
		
		//// ilk hesapları oluşturuldu
		$varmi_  = $this->db->get_where("hesaplar", array("user_id"=>$user_id, "hesap"=>"Kasa"));
	
		if($varmi_->num_rows()<1)
			$this->ilk_user_kurulum();
		
	
		$data["urun_adet"] = $this->db->get_where("urunler",array("user_id"=>$user_id,"aktif"=>0))->num_rows();		
		
		$data["musteri_adet"] = $this->db->get_where("musteriler",array("user_id"=>$user_id,"aktif"=>0))->num_rows();		
		
		$data["firma_adet"] = $this->db->get_where("firmalar",array("user_id"=>$user_id,"aktif"=>0))->num_rows();	
	
		$this->db->like('user_id', $user_id);
		$data["satis_adet"] = $this->db->count_all_results('satislar');
	
		$data["bos"] = $this->session->userdata('user_id');
		$data["uyarilar"] = $this->db->get_where("uyarilar", array("user_id"=>$data["bos"], "aktif !="=>0)); 
		
		$this->load->helper('date');						
		$tarih = '%d/%m/%Y';
		
		if($this->input->get("t1"))
		{
			
		}
		if($this->input->get("t2"))
		{
			
		}
		
		$zaman = time();
		$data["bugun"] =  mdate($tarih, $zaman);
		
		if($this->input->get("sonuc"))
			$data["sonuc"] = $this->input->get("sonuc"); 
		
		$this->load->view('header', $data);
		$this->load->view('sol');
		$this->load->view('ust');
		$this->load->view('ana_sayfa');
		$this->load->view('footer');
		
	}
	
	
	
	/*   --------------------------   - -       Muhasebe Sistemi    --   --------------------------------------------- */
	
	public function kasa()
	{
		$this->session_control();
		$user_id = $this->session->userdata('user_id');	
		$data["bos"] = $user_id;
		$data["cari"] = NULL; 
		
		/////// Tarih aralığını bulup veri tabanından çeker.. 
		if($this->input->get("baslangic") || $this->input->get("bitis") )
		{
			$baslangic = $this->input->get("baslangic");
			$bitis = $this->input->get("bitis");
			
			if($this->input->get("baslangic") =="" ||  !$this->input->get("baslangic"))
				$baslangic = "01/01/".date('Y');
			  
			if($this->input->get("bitis") =="" || !$this->input->get("bitis") ) 
				$bitis = "31/12/".date('Y');
			
			//echo $baslangic." tarihinden ".$bitis."tarihine kadar seçildi";
			$zaman_ar = $this->tarihbul($baslangic,$bitis);
			
			$caris = $this->db->get_where("cari", array("user_id"=>$user_id));
			$yeni_caris = array();
			foreach ($caris->result() as $row)
			{
				if (in_array($row->tarih, $zaman_ar)) 
				{ /// Bu aralıktaysa ekle
					array_push($yeni_caris, $row);
				}
			}
			$yepyeni = $this->tarih_siralamasi($yeni_caris);
			//// buraya tarih sıralama fonksiyonu ekleyip return array olmalı 
			$data["cari"] =  $yeni_caris;
		}else{
			$data["cari"] =  $this->db->get_where("cari", array("user_id"=>$user_id))->result();
		}			
		
		
		$hesaplar = $this->db->order_by('tip_id', 'ASC')->get_where("hesaplar", array("user_id"=>$data["bos"]));		
		$nakit_gelir =0;
		$nakit_gider=0;
		$nakit_alacak=0;
		$nakit_borc=0;
		
		$banka_gelir =0;
		$banka_gider=0;
		$banka_alacak=0;
		$banka_borc=0;
		
		$alinan_cek_gelir =0;
		$alinan_cek_gider=0;
		$alinan_cek_alacak=0;
		$alinan_cek_borc=0;
		
		$yazilan_cek_gelir =0;
		$yazilan_cek_gider=0;
		$yazilan_cek_alacak=0;
		$yazilan_cek_borc=0;
		
		$alinan_senet_gelir =0;
		$alinan_senet_gider=0;
		$alinan_senet_alacak=0;
		$alinan_senet_borc=0;
		
		$yazilan_senet_gelir =0;
		$yazilan_senet_gider=0;
		$yazilan_senet_alacak=0;
		$yazilan_senet_borc=0;
		
		
		
		
		foreach($data["cari"]  as $row)
		{
			if($row->tip_id == 1)
			{
				$nakit_gelir += $row->gelir; 
				$nakit_gider += $row->gider; 
				$nakit_alacak += $row->alacak; 
				$nakit_borc += $row->borc; 
			}				
			
			if($row->tip_id == 2)
			{
				$banka_gelir += $row->gelir; 
				$banka_gider += $row->gider; 
				$banka_alacak += $row->alacak; 
				$banka_borc += $row->borc; 
			}				
			
			if($row->tip_id == 3)
			{
				$alinan_cek_gelir += $row->gelir; 
				$alinan_cek_gider += $row->gider; 
				$alinan_cek_alacak += $row->alacak; 
				$alinan_cek_borc += $row->borc; 
			}				
			
			if($row->tip_id == 4)
			{
				$alinan_senet_gelir += $row->gelir; 
				$alinan_senet_gider += $row->gider; 
				$alinan_senet_alacak += $row->alacak; 
				$alinan_senet_borc += $row->borc; 
			}				
			
			if($row->tip_id == 5)
			{
				$yazilan_cek_gelir += $row->gelir; 
				$yazilan_cek_gider += $row->gider; 
				$yazilan_cek_alacak += $row->alacak; 
				$yazilan_cek_borc += $row->borc; 
			}				
			
			if($row->tip_id == 6)
			{
				$yazilan_senet_gelir += $row->gelir; 
				$yazilan_senet_gider += $row->gider; 
				$yazilan_senet_alacak += $row->alacak; 
				$yazilan_senet_borc += $row->borc; 
			}				
	
		}
			
			$nakit_al =0;
			$nakit_bor=0;
			$banka_al=0;
			$banka_bor=0;
		foreach($hesaplar->result() as $row2)
		{
			$top_banka_al =0;
			$top_nakit_al =0;
			$top_banka_gel=0;
			$top_nakit_gel=0;
			$top_banka_borc=0;
			$top_nakit_borc=0;
			$top_banka_gider=0;
			$top_nakit_gider=0;
			
			foreach($data["cari"] as $row)
			{	
				if($row->hesap_id == $row2->id)
				{ 
					if($row->gelir == $row->alacak && $row->gelir !="" || $row->gider == $row->borc && $row->gider !="")
					{
						
					}else{
						//if($row->tip_id == 1)
					//	{
							$top_nakit_al += $row->alacak;
							$top_nakit_gel += $row->gelir;
							$top_nakit_borc+= $row->borc;
							$top_nakit_gider+= $row->gider;
					//	}
						/* if($row->tip_id == 2)
						{
							$top_banka_al += $row->alacak;
							$top_banka_gel += $row->gelir;
							$top_banka_borc+= $row->borc;
							$top_banka_gider+= $row->gider;
						} */
					}
				}
			}
			$nak_al =  abs($top_nakit_al + $top_nakit_gel);
			$nak_bor = abs($top_nakit_borc+$top_nakit_gider);
			
		//	$ban_al =  abs($top_banka_al + $top_banka_gel);
			//$ban_bor = abs($top_banka_borc+$top_banka_gider);
			
			if($nak_al<$nak_bor)
			{
				$nakit_al += $nak_bor - $nak_al; 
			}else{
				$nakit_bor += $nak_al - $nak_bor;
			}
			
			/* if($ban_al<$ban_bor)
			{
				$banka_al += $ban_bor - $ban_al; 
			}else{
				$banka_bor += $ban_al - $ban_bor;
			} */
				
		}
		
		
		$this->load->helper('date');						
		$tarih = '%d/%m/%Y';
		$zaman = time();
		$bugun =  mdate($tarih, $zaman);	
		$data["nakit"] = $nakit_gelir - $nakit_gider;
		$data["banka"] = $banka_gelir - $banka_gider;
		$data["cek"] = $alinan_cek_gelir-($alinan_cek_gider+$yazilan_cek_gider);
		$data["senet"] = $alinan_senet_gelir-($alinan_senet_gider+$yazilan_senet_gider);
		
		$data["gelir_nakit"] = $nakit_gelir ;
		$data["gelir_banka"] = $banka_gelir;
		$data["gelir_cek"] = $alinan_cek_gelir;
		$data["gelir_senet"] = $alinan_senet_gelir;
		
		$data["gider_nakit"] = $nakit_gider;
		$data["gider_banka"] = $banka_gider;
		$data["gider_cek"] = $alinan_cek_gider; 
		$data["gider_senet"] = $alinan_senet_gider;
		$data["alacak_nakit"] = $nakit_al;
		$data["alacak_banka"] = $banka_al;
		$data["alacak_cek"] = $alinan_cek_alacak;
		$data["alacak_senet"] = $alinan_senet_alacak;
		
		
		$data["borc_nakit"] = $nakit_bor;
		$data["borc_banka"] = $banka_bor;
		$data["borc_cek"] = $yazilan_cek_borc;
		$data["borc_senet"] = $yazilan_senet_borc;
		
		
		
		
		if($this->input->get("sonuc"))
			$data["sonuc"] = $this->input->get("sonuc"); 
		
		$this->load->view('header', $data);
		$this->load->view('sol');
		$this->load->view('ust');
		$this->load->view('kasa');
		$this->load->view('footer');
		
	}
	
	public function alacaklar()
	{
		
	$this->session_control();
		$data["user"] =  $this->session->userdata('user_id');
		$carr =  $this->db->get_where("cari", array("user_id"=>$data["user"]));
		$data["cari"] =  $this->tarih_sira($carr);
		$data["hesaplar"] = $this->db->order_by('tip_id', 'ASC')->get_where("hesaplar", array("user_id"=>$data["user"]));
		
		
		$this->db->where('durum =', -1 );
		$this->db->or_where('durum =', 1);
		$data["tahsiller"]  = $this->db->get('hesap_tipleri');
		
		
		$veri["hesap_id"]	 	= $this->input->post("hesap_id");
		$veri["tip_id"]	 	= $this->input->post("tip_id");
		$veri["aciklama"]		= $this->input->post("aciklama");
		//$veri["gelir"] 			= $this->int_tutar( $this->input->post("gelir"));
		//$veri["gider"]			= $this->int_tutar( $this->input->post("gider"));
		//$veri["alacak"]			= $this->int_tutar( $this->input->post("alacak"));
		$veri["borc"]			=$this->int_tutar(  $this->input->post("alacak"));
		$veri["tarih"]				= $this->input->post("tarih");
		$veri["vade"]			= $this->input->post("vade");
		
		$veri["user_id"]	= $data["user"];
		
		
		if($this->input->post("hesap_id"))
		{
			$this->load->model('db_islem');
			if($this->input->get("m"))
			{
				$ekleme 				= $this->db_islem->duzenle("cari", $this->input->get("m"),  $veri);
			}else{
				$ekleme 				= $this->db_islem->ekle("cari", $veri, "hepsi");
			}
			$data["sonuc"] 	= $ekleme; 
		}
		
		if($this->input->get("m"))
		{
			$data["veriler"] = $this->db->get_where("cari", array("user_id"=>$data["user"], "id"=>$this->input->get("m") ))->result()[0];
		}
		if($this->input->get("id"))
		{
			// $this->islem_sil1($this->input->get("id"));
		}
		
		$this->load->view('header', $data);
		$this->load->view('sol');
		$this->load->view('ust');
		$this->load->view('alacaklar');
		$this->load->view('footer');
	}
	
	
	public function borclar()
	{
		
	$this->session_control();
		$data["user"] =  $this->session->userdata('user_id');
	
		$carr = $this->db->get_where("cari", array("user_id"=>$data["user"]));
		$data["cari"] =  $this->tarih_sira($carr);
		
		$data["hesaplar"] = $this->db->order_by('tip_id', 'ASC')->get_where("hesaplar", array("user_id"=>$data["user"]));
		
		
		$this->db->where('durum =', -1 );
		$this->db->or_where('durum =', 0);
		$data["tahsiller"]  = $this->db->get('hesap_tipleri');
		
		
		$veri["hesap_id"]	 	= $this->input->post("hesap_id");
		$veri["tip_id"]	 	= $this->input->post("tip_id");
		$veri["aciklama"]		= $this->input->post("aciklama");
		//$veri["gelir"] 			= $this->int_tutar( $this->input->post("gelir"));
		//$veri["gider"]			= $this->int_tutar( $this->input->post("gider"));
		$veri["alacak"]		= $this->int_tutar( $this->input->post("borc"));
		//$veri["borc"]			= $this->int_tutar( $this->input->post("borc"));
		$veri["tarih"]				= $this->input->post("tarih");
		$veri["vade"]			= $this->input->post("vade");
		
		$veri["user_id"]	= $data["user"];
		
		
		if($this->input->post("hesap_id"))
		{
			$this->load->model('db_islem');
			if($this->input->get("m"))
			{
				$ekleme 				= $this->db_islem->duzenle("cari", $this->input->get("m"),  $veri);
			}else{
				$ekleme 				= $this->db_islem->ekle("cari", $veri, "hepsi");
			}
			$data["sonuc"] 	= $ekleme; 
		}
		
		if($this->input->get("m"))
		{
			$data["veriler"] = $this->db->get_where("cari", array("user_id"=>$data["user"], "id"=>$this->input->get("m") ))->result()[0];
		}
		if($this->input->get("id"))
		{
			// $this->islem_sil1($this->input->get("id"));
		}
		
		$this->load->view('header', $data);
		$this->load->view('sol');
		$this->load->view('ust');
		$this->load->view('borclar');
		$this->load->view('footer');
	}
	
	public function gelirler()
	{
		
		$this->session_control();
		$data["user"] =  $this->session->userdata('user_id');
		$carr =  $this->db->get_where("cari", array("user_id"=>$data["user"], "gelir !="=>""));
		$data["cari"] =  array_reverse($this->tarih_sira($carr));
		$data["hesaplar"] = $this->db->order_by('tip_id', 'ASC')->get_where("hesaplar", array("user_id"=>$data["user"]));
		$data["tahsiller"] =  $this->db->get_where("hesap_tipleri", array("durum"=>-1));
			
		$veri["hesap_id"]	 	= $this->input->post("hesap_id");
		$veri["tip_id"]	 		= $this->input->post("tip_id");// işlenen hesap
		$veri["aciklama"]		= $this->input->post("aciklama");
		$veri["gelir"] 			= $this->int_tutar( $this->input->post("gelir"));
		//$veri["gider"]			= $this->input->post("gider");
		if($this->input->post("cift") )
		{
			
			$hesabin_tipi			= $this->db->get_where("hesaplar", array("id"=>$veri["hesap_id"]))->result()[0]->tip_id;
			if($hesabin_tipi>=7){
				$veri["alacak"]			= $veri["gelir"] ;
			}
		}
		//$veri["borc"]			= $this->input->post("borc");
		$veri["tarih"]				= $this->input->post("tarih");
		$veri["vade"]			= $this->input->post("vade");
		$veri["user_id"]	= $data["user"];
		
		
		if($this->input->post("hesap_id"))
		{
			$this->load->model('db_islem');
			$hesabin_tipi			= $this->db->get_where("hesaplar", array("id"=>$veri["hesap_id"]))->result()[0]->tip_id;
			if($this->input->get("m"))
			{
					if($hesabin_tipi<7)
					{
						$bagli_id  = $this->db->get_where("cari", array("id"=>$this->input->get("m")))->result()[0]->baglantili_id;
						$veriler["gider"] = $veri["gelir"] ;
						$veriler["borc"] = $veri["gelir"] ;
						$veri["alacak"] = $veri["gelir"] ;
						$veriler["aciklama"] = $veri["aciklama"] ;
						$veriler["tarih"] = $veri["tarih"] ;
						$ekleme 				= $this->db_islem->duzenle("cari", $bagli_id,  $veriler);
					}
						$ekleme 				= $this->db_islem->duzenle("cari", $this->input->get("m"),  $veri);
			}else{
				if($hesabin_tipi<7) 
					{
						$veriler["hesap_id"]	 	= $this->input->post("hesap_id");
						$veriler["gider"] = $veri["gelir"] ;
						$veriler["borc"] = $veri["gelir"] ;
						$veri["alacak"] = $veri["gelir"] ;
						$veriler["user_id"] = $data["user"];
						$veriler["tip_id"] = $hesabin_tipi;
						$veriler["aciklama"] = $veri["aciklama"] ;
						$veriler["tarih"] = $veri["tarih"] ;
						$veri["baglantili_id"]	= $this->db_islem->ekle("cari", $veriler, 1);
						$veri["hesap_id"]	 	= $this->db->get_where("hesaplar", array("tip_id"=>$veri["tip_id"], "user_id"=>$data["user"]))->result()[0]->id;
						$veri2["baglantili_id"]	= $this->db_islem->ekle("cari", $veri, 1);
						$ekleme 				= $this->db_islem->duzenle("cari", $veri["baglantili_id"],  $veri2);
					}else{
							
						$ekleme = $this->db_islem->ekle("cari", $veri, "hepsi");
					}
			}
			$data["sonuc"] 	= $ekleme; 
		}
		
		if($this->input->get("m"))
		{
			$data["veriler"] = $this->db->get_where("cari", array("user_id"=>$data["user"], "id"=>$this->input->get("m") ))->result()[0];
		}
		if($this->input->get("id"))
		{
			$silincek =  $this->db->get_where("cari", array("id"=>$this->input->get("id"), "baglantili_id"=>0));
			if($silincek->result())
			{
				$data["sonuc"] = $this->sil($this->input->get("id"), "cari"); 
				$adres = base_url()."gelirler?sonuc=".$data["sonuc"]; 
				header("Location: $adres");
			}
			// $this->islem_sil1($this->input->get("id"));
		}
		
		$this->load->view('header', $data);
		$this->load->view('sol');
		$this->load->view('ust');
		$this->load->view('gelirler');
		$this->load->view('footer');
	}
	
	public function giderler()
	{
		
		$this->session_control();
		$data["user"] =  $this->session->userdata('user_id');
		$carr =  $this->db->get_where("cari", array("user_id"=>$data["user"], "gider !="=>""));
		$data["cari"] =  array_reverse($this->tarih_sira($carr));
		
		$data["hesaplar"] = $this->db->order_by('tip_id', 'ASC')->get_where("hesaplar", array("user_id"=>$data["user"]));
		
		
		$this->db->where('durum =', -1 );
		$this->db->or_where('durum =', 0);
		$data["tahsiller"]  = $this->db->get('hesap_tipleri');
		
		
		$veri["hesap_id"]	 	= $this->input->post("hesap_id");
		$veri["tip_id"]	 	= $this->input->post("tip_id");
		$veri["aciklama"]		= $this->input->post("aciklama");
		//$veri["gelir"] 			= $this->int_tutar( $this->input->post("gelir"));
		$veri["gider"]			= $this->int_tutar( $this->input->post("gider"));
		
		if($this->input->post("cift") )
		{
			$hesabin_tipi			= $this->db->get_where("hesaplar", array("id"=>$veri["hesap_id"]))->result()[0]->tip_id;
			if($hesabin_tipi>=7)
				$veri["borc"]			= $veri["gider"] ;
		}
		
		
		
		//$veri["alacak"]			= $this->input->post("alacak");
		//$veri["borc"]			= $this->input->post("borc");
		$veri["tarih"]				= $this->input->post("tarih");
		$veri["vade"]			= $this->input->post("vade");
		
		$veri["user_id"]	= $data["user"];
		
		
		if($this->input->post("hesap_id"))
		{
			$this->load->model('db_islem');
			$hesabin_tipi			= $this->db->get_where("hesaplar", array("id"=>$veri["hesap_id"]))->result()[0]->tip_id;
			if($this->input->get("m"))
			{
					if($hesabin_tipi<7)
					{
						$bagli_id  = $this->db->get_where("cari", array("id"=>$this->input->get("m")))->result()[0]->baglantili_id;
						$veriler["gelir"] = $veri["gider"] ;
						$veriler["alacak"] = $veri["gider"] ;
						$veri["borc"] = $veri["gider"] ;
						$veriler["aciklama"] = $veri["aciklama"] ;
						$veriler["tarih"] = $veri["tarih"] ;
						$ekleme 				= $this->db_islem->duzenle("cari", $bagli_id,  $veriler);
					}
						$ekleme 				= $this->db_islem->duzenle("cari", $this->input->get("m"),  $veri);
			}else{
				if($hesabin_tipi<7)
					{
						$veriler["hesap_id"]	 	= $this->input->post("hesap_id");
						$veriler["gelir"] = $veri["gider"] ;
						$veriler["alacak"] = $veri["gider"] ;
						$veri["borc"] = $veri["gider"] ;
						$veriler["user_id"] = $data["user"];
						$veriler["tip_id"] = $hesabin_tipi;
						$veriler["aciklama"] = $veri["aciklama"] ;
						$veriler["tarih"] = $veri["tarih"] ;
						$veri["baglantili_id"]	= $this->db_islem->ekle("cari", $veriler, 1);
						$veri["hesap_id"]	 	= $this->db->get_where("hesaplar", array("tip_id"=>$veri["tip_id"], "user_id"=>$data["user"]))->result()[0]->id;
						$veri2["baglantili_id"]	= $this->db_islem->ekle("cari", $veri, 1);
						$ekleme 				= $this->db_islem->duzenle("cari", $veri["baglantili_id"],  $veri2);
					}else{
						$ekleme = $this->db_islem->ekle("cari", $veri, "hepsi");
					}
			}
			$data["sonuc"] 	= $ekleme; 
		}
		
		if($this->input->get("m"))
		{
			$data["veriler"] = $this->db->get_where("cari", array("user_id"=>$data["user"], "id"=>$this->input->get("m") ))->result()[0];
		}
		if($this->input->get("id"))
		{
			// $this->islem_sil1($this->input->get("id"));
		}
		
		$this->load->view('header', $data);
		$this->load->view('sol');
		$this->load->view('ust');
		$this->load->view('giderler');
		$this->load->view('footer');
	}
	
	
	public function hesap()
	{
		
		$this->session_control();
		$data["user"] =  $this->session->userdata('user_id');
		$data["hesaplar"] =  $this->db->get_where("hesaplar", array("user_id"=>$data["user"]));
		
		$this->db->where('user_id =', $data["user"] );
		$this->db->or_where('durum =', 1);
		$data["hesap_tipleri"] = $this->db->get('hesap_tipleri');
		
		$veri["tip_id"]	 	= $this->input->post("hesap_tipleri");
		$veri["hesap"]		= $this->input->post("hesap");
		$veri["tc_vergi"] 	= $this->input->post("tc");
		$veri["tel1"]			= $this->input->post("tel1");
		$veri["tel2"]			= $this->input->post("tel2");
		$veri["mail"]			= $this->input->post("mail");
		$veri["adres"]		= $this->input->post("adres");
		$veri["user_id"]	= $data["user"];
		
		

		
		if($this->input->post("hesap_tipleri"))
		{
			$this->load->model('db_islem');
			if($this->input->get("m"))
			{
				$ekleme 				= $this->db_islem->duzenle("hesaplar", $this->input->get("m"),  $veri);
			}else{
				$ekleme 				= $this->db_islem->ekle("hesaplar", $veri, "hepsi");
			}
			$data["sonuc"] 	= $ekleme; 
		}
		
		if($this->input->get("m"))
		{
			$data["veriler"] = $this->db->get_where("hesaplar", array("user_id"=>$data["user"], "id"=>$this->input->get("m") ))->result()[0];
		}
		
		
		if($this->input->get("id"))
		{
			$silincek=$this->input->get("m");
			$caride_varmi = $this->db->get_where("cari", array("hesap_id"=>$silincek));
			if($caride_varmi->result())
			{
				$data["sonuc"] = "Bu hesapla ilgili cari kayıtlarını silmeden hesabı silemezsiniz.. ";
			}else{
				$data["sonuc"] = $this->sil($this->input->get("id"), "hesaplar"); 
				$adres = base_url()."hesap?sonuc=".$data["sonuc"]; 
				header("Location: $adres");
				//$data["sonuc"] = "Hesap başarıyla silinmiştir. ";
			}
			
		
		}
			
			
		$this->load->view('header', $data);
		$this->load->view('sol');
		$this->load->view('ust');
		$this->load->view('hesap');
		$this->load->view('footer');
		
	}
		
	
	/*   --------------------------   - -       Stok Takip Sistemi    --   --------------------------------------------- */
	
	public function musteri_ekle()
	{ 
		$this->session_control();
		
		$data["bos"] = "";
		$user_id = $this->session->userdata('user_id');
		if( $this->input->post("isim") )
		{
			$isim = $this->input->post("isim");
			$mail = $this->input->post("mail");
			$tel = $this->input->post("tel");
			$tel2 = $this->input->post("tel2");
			$il = $this->input->post("il");
			$ilce = $this->input->post("ilce");
			$adres = $this->input->post("adres");
			$d_tarih = $this->input->post("d_tarih");
			$tarih = $this->input->post("tarih");
			$tc_ = $this->input->post("tc_");
			
			
			
			$this->load->model('db_islem');
			$veri = array ( "tc" => $tc_, "d_tarih"=>$d_tarih, "isim" => $isim, "tel"=> $tel, "tel2"=> $tel2, "il"=>$il, "ilce"=>$ilce, "mail"=>$mail, "adres"=>$adres, "tarih"=> $tarih, "user_id"=>$user_id );
			/////////////////////////////////////////////////
			if($this->baglimi)
			{
				$veri_ = array (  "tip_id"=>10, "hesap" => $isim, "tel1"=> $tel, "tel2"=> $tel2, "mail"=>$mail, "adres"=>$adres,  "user_id"=>$user_id );
			}
			/////////////////////////////////////////////////
			if($this->input->get("m"))
			{
				$id =$this->input->get("m") ;
				$kontrol = $this->db->get_where("musteriler", array('user_id' => $user_id, 'id' => $id));
				
				if($kontrol->result())
				{
						$data[ "sonuc" ]  = "Hasta kaydınız başarı ile güncellenmiştir. "; 
						$ekleme = $this->db_islem->duzenle("musteriler",$id, $veri);
						if($this->baglimi)
						{
							$id2 = $kontrol->result()[0]->baglanti_id;
							$e4 = $this->db_islem->duzenle("hesaplar",$id2, $veri_);
						}
				}else{
						$data[ "sonuc" ]  = "Hata !! Lütfen öncelikle bilgilerini düzenlemek istediğiniz müşteriyi seçiniz."; 
				}
				
			}else{
				 $kont = $this->db->get_where("musteriler", array("tc" => $tc_ ))->result(); 
				 if($kont){
					$data[ "sonuc" ]  = "Bu TC Numarası ile başka bir kayıt mevcuttur. Düzenleme kısmından işlem yapabilirsiniz.  ";
				 }else{
					
					/////////////////////////////////////////////////
					if($this->baglimi)
					{
						
						$id1=  $this->db_islem->ekle("musteriler", $veri, 1);
						$id2= $this->db_islem->ekle("hesaplar", $veri_, 1);
						
						$veri["baglanti_id"] = $id2;
						$veri_["baglanti_id"] = $id1;
						$e3 = $this->db_islem->duzenle("musteriler",$id1, $veri);
						$e4 = $this->db_islem->duzenle("hesaplar",$id2, $veri_);
					}else{
						$ekleme = $this->db_islem->ekle("musteriler", $veri, "hepsi");
					}
					/////////////////////////////////////////////////
					
					$data[ "sonuc" ]  = "Müşteri kaydınız başarı ile gerçekleşti. ";
				 }
			}//Ekleme Sonu	
		}// Post sonu
		
		if($this->input->get("m"))
		{
			$id =$this->input->get("m") ;
			$kontrol = $this->db->get_where("musteriler", array('user_id' => $user_id, 'id' => $id));
			if($kontrol->result())
			{
				$ko = $kontrol->result();
				$k = $ko[0];
				$data["veriler"] = $k; 
			}
			if($this->input->post("dosya_ismi"))
			{
				$this->load->helper(array('form', 'url'));
				$config['upload_path']         = './uploads/';
                $config['allowed_types']      = 'gif|jpg|png';
                $config['max_size']             = 8000;
                $config['file_name']             = $this->input->post("dosya_ismi")."_".$id;
               
                $this->load->library('upload', $config);
				if($this->upload->do_upload("dosya"))
				{
					$d_isim = $this->input->post("dosya_ismi"); 
					$resim =$this->upload->data('file_name'); 
					$veri = array("musteri_id"=> $id, "dosya_ismi"=>$d_isim, "resim"=>$resim);
					$this->load->model('db_islem');
					$ekleme = $this->db_islem->ekle("uploads", $veri, "hepsi");
				}
				else
				{
				   $data["sonuc"] = "Dosya yüklenirken hata ile karşılaşıldı, Hata Kodu : " . $this->upload->display_errors('<p> Hata!', '</p>');
				}
				
			}
			
			if($this->input->get("sil"))
			{
				$silincek =  $this->input->get("sil"); 
				$resim_sil = $this->db->get_where("uploads", array("id"=>$silincek))->result()[0]; 
				$path = "./uploads/".$resim_sil->resim;
				echo $path;
				$this->load->model('db_islem');
				unlink($path);
				$sil = $this->db_islem->sil("uploads", $silincek);
				
				$adres = current_url()."?m=".$id;
				header("Location: $adres");
			}
			
			
		}
		
		
		$this->load->view('header', $data);
		$this->load->view('sol');
		$this->load->view('ust');
		$this->load->view('musteri_ekle');
		$this->load->view('footer');
		
		
	}
	
	public function firma_ekle()
	{
		$this->session_control();
		
		$data["bos"] = "";
		$user_id = $this->session->userdata('user_id');
		if( $this->input->post("firma") )
		{
			$firma = $this->input->post("firma");
			$mail = $this->input->post("mail");
			$tel = $this->input->post("tel");
			$tel2 = $this->input->post("tel2");
			$adres = $this->input->post("adres");
			$tarih = $this->input->post("tarih");
			
			
			
			$this->load->model('db_islem');
			$veri = array (  "firma" => $firma, "tel"=> $tel, "tel2"=> $tel2, "mail"=>$mail, "adres"=>$adres, "tarih"=> $tarih, "user_id"=>$user_id );
			/////////////////////////////////////////////////
			if($this->baglimi)
			{
				$veri_ = array (  "tip_id"=>11, "hesap" => $firma, "tel1"=> $tel, "tel2"=> $tel2, "mail"=>$mail, "adres"=>$adres,  "user_id"=>$user_id );
			}
			/////////////////////////////////////////////////
			if($this->input->get("m"))
			{
				$id =$this->input->get("m") ;
				$kontrol = $this->db->get_where("firmalar", array('user_id' => $user_id, 'id' => $id));
			
				if($kontrol->result())
				{
						$data[ "sonuc" ]  = "Firma kaydınız başarı ile güncellenmiştir. "; 
						$ekleme = $this->db_islem->duzenle("firmalar",$id, $veri);
						if($this->baglimi)
						{
							$id2 = $kontrol->result()[0]->baglanti_id;
							$e4 = $this->db_islem->duzenle("hesaplar",$id2, $veri_);
						}
				}else{
						$data[ "sonuc" ]  = "Hata !! Lütfen öncelikle bilgilerini düzenlemek istediğiniz firmayı seçiniz."; 
				}
				
			}else{
				$data[ "sonuc" ]  = "Firma kaydınız başarı ile oluşturuldu. "; 
				/////////////////////////////////////////////////
				if($this->baglimi)
				{
					
					$id1=  $this->db_islem->ekle("firmalar", $veri, 1);
					$id2= $this->db_islem->ekle("hesaplar", $veri_, 1);
					
					$veri["baglanti_id"] = $id2;
					$veri_["baglanti_id"] = $id1;
					$e3 = $this->db_islem->duzenle("firmalar",$id1, $veri);
					$e4 = $this->db_islem->duzenle("hesaplar",$id2, $veri_);
				}else{
					$ekleme = $this->db_islem->ekle("firmalar", $veri, "hepsi");
				}
				/////////////////////////////////////////////////
			}//Ekleme Sonu	
		}// Post sonu
		
		if($this->input->get("m"))
		{
			$id =$this->input->get("m") ;
			$kontrol = $this->db->get_where("firmalar", array('user_id' => $user_id, 'id' => $id));
			if($kontrol->result())
			{
				$ko = $kontrol->result();
				$k = $ko[0];
				$data["veriler"] = $k; 
			}
		}
		
		$this->load->view('header', $data);
		$this->load->view('sol');
		$this->load->view('ust');
		$this->load->view('firma_ekle');
		$this->load->view('footer');
		
	}
	public function urun_ekle()
	{
		$this->session_control();
		
		$data["bos"] = $this->session->userdata('user_id');
		$user_id = $this->session->userdata('user_id');
		$this->db->like('user_id', $user_id);
		$urun_toplam = $this->db->count_all_results('urunler');
		$data["toplam_urun"] = $urun_toplam + 1;
		
		if( $this->input->post("barkod") )
		{
			$barkod = $this->input->post("barkod");
			$urun = $this->input->post("urun");
			//$adet = $this->input->post("adet");
			$firma = $this->input->post("firma");
			$alis =$this->int_tutar( $this->input->post("alis"));
			$satis = $this->int_tutar( $this->input->post("satis"));
			$tarih = $this->input->post("tarih");
			
			$this->load->model('db_islem');
			$veri = array (  "barkod" => $barkod, "urun" => $urun, "firma"=>$firma, "alis"=>$alis, "satis"=>$satis, "tarih"=> $tarih, "user_id"=>$user_id );
			
			if($this->input->get("m"))
			{
				$id =$this->input->get("m") ;
				$kontrol = $this->db->get_where("urunler", array('user_id' => $user_id, 'id' => $id));
			
				if($kontrol->result())
				{
						$data[ "sonuc" ]  = "Ürün kaydınız başarı ile güncellenmiştir. "; 
						$ekleme = $this->db_islem->duzenle("urunler",$id, $veri);
				}else{
						$data[ "sonuc" ]  = "Hata !! Lütfen öncelikle bilgilerini düzenlemek istediğiniz ürünü seçiniz."; 
				}
				
			}else{
				$data[ "sonuc" ]  = "Ürün kaydınız başarı ile oluşturuldu. "; 
				$ekleme 						= $this->db_islem->ekle("urunler", $veri, "hepsi");
			}//Ekleme Sonu	
		}// Post sonu
		
		if($this->input->get("m"))
		{
			$id =$this->input->get("m") ;
			$kontrol = $this->db->get_where("urunler", array('user_id' => $user_id, 'id' => $id));
			if($kontrol->result())
			{
				$ko = $kontrol->result();
				$k = $ko[0];
				$data["veriler"] = $k; 
			}
		}
		
		$this->load->view('header', $data);
		$this->load->view('sol');
		$this->load->view('ust');
		$this->load->view('urun_ekle');
		$this->load->view('footer');
		
	}
	public function stok_ekle()
	{
		$this->session_control();
		
		$data["bos"] = $this->session->userdata('user_id');
		$user_id = $this->session->userdata('user_id');
		$this->db->like('user_id', $user_id);
		$urun_toplam = $this->db->count_all_results('urunler');
		$data["toplam_urun"] = $urun_toplam + 1;
		$alacak_bakiyesi = false;
		
		
		if( $this->input->post("urun") )
		{
			
			$firma =  $this->input->post("firma");
			$urun =  $this->input->post("urun");
			$adet = $this->input->post("adet");
			$alis = $this->int_tutar( $this->input->post("alis"));
			$odenen = $this->int_tutar( $this->input->post("odenen"));
			
			
			$tarih = $this->input->post("tarih");
			
			$this->load->model('db_islem');
			
			$veri = array (   "u_id" => $urun, "adet"=> $adet,  "alis"=>$alis, "odenen"=>$odenen, "tarih"=> $tarih );
				/////////////////////////////////////////////////
			if($this->baglimi)
			{
				$borc_  = $alis - $odenen;
				if($borc_ >0 )
					$alacak_bakiyesi =true; 
				
				$hes_id = $this->db->get_where("firmalar", array("id"=>$firma))->result()[0]->baglanti_id;
				$ur = $this->db->get_where("urunler", array("id"=>$urun))->result()[0]->urun;
				$veri_ = array (  "hesap_id"=>$hes_id, "tip_id"=>1, "aciklama"=>$adet." ".$ur." Stok alımı yapılmıştır.", "gider"=>$odenen, "borc"=>$odenen, "tarih"=>$tarih, "user_id"=>$user_id );
				$veri_2 = array (  "hesap_id"=>$hes_id, "tip_id"=>1, "aciklama"=>$adet." ".$ur." Stok alımı yapılmıştır. Ödeme sonrası kalan tutardır.",  "alacak"=>$borc_, "tarih"=>$tarih, "user_id"=>$user_id );
			}
			/////////////////////////////////////////////////
			
			if(!$this->bos_varmi(array($urun,$adet,$tarih)))
			{
				if($this->input->get("m"))
				{
					$data[ "sonuc" ]  = "Stok kaydınız başarı ile düzenlendi. "; 
					$ekleme 						= $this->db_islem->duzenle("stoklar", $this->input->get("m"), $veri);
				}else{
					/////////////////////////////////////////////////
				if($this->baglimi)
				{
					
					$id1=  $this->db_islem->ekle("stoklar", $veri, 1);
					$id2= $this->db_islem->ekle("cari", $veri_, 1);
					if($alacak_bakiyesi)
						$id3= $this->db_islem->ekle("cari", $veri_2, 1);
					
					$veri["baglanti_id"] = $id2;
					$veri_["baglantili_id"] = $id1;
					$veri_2["baglantili_id"] = $id1;
					
					
					$e3 = $this->db_islem->duzenle("stoklar",$id1, $veri);
					$e4 = $this->db_islem->duzenle("cari",$id2, $veri_);
					if($alacak_bakiyesi)
						$e5 = $this->db_islem->duzenle("cari",$id3, $veri_2);
					
				}else{
					$ekleme = $this->db_islem->ekle("stoklar", $veri, "hepsi");
				}
				/////////////////////////////////////////////////
					$data[ "sonuc" ]  = "Stok kaydınız başarı ile oluşturuldu. "; 
					
				}
			}else{
				$data[ "sonuc" ]  = "Lütfen Boş Alan Bırakmayınız.."; 
			} 
			
		}// Post sonu
		
		if($this->input->get("m"))
		{
			$id =$this->input->get("m") ;
			$kontrol = $this->db->get_where("stoklar", array('id' => $id));
			if($kontrol->result())
			{
				$ko = $kontrol->result();
				$k = $ko[0];
				$data["veriler"] = $k; 
			}
		}
		
		$this->load->view('header', $data);
		$this->load->view('sol');
		$this->load->view('ust');
		$this->load->view('stok_ekle');
		$this->load->view('footer');
		
	}
	
	public function tamire_gonder()
	{
		$this->session_control();
		
		$data["bos"] = $this->session->userdata('user_id');
		$user_id = $this->session->userdata('user_id');
		
		if($this->input->get("m"))
		{
			$id =$this->input->get("m") ;
			$kontrol = $this->db->get_where("tamir", array('id' => $id));
			if($kontrol->result())
			{
				$ko = $kontrol->result();
				$k = $ko[0];
				$data["veriler"] = $k; 
			}
		}
		
		if( $this->input->post("urun") )
		{
			
			$marka =  $this->input->post("firma");
			$urun =  $this->input->post("urun");
			$musteri =  $this->input->post("musteri");
			$gonderim =  $this->input->post("gonderim");
			$not_ =  $this->input->post("not_");
			$ucret =  $this->input->post("ucret");
			$durum =  $this->input->post("durum");
			$tarih = $this->input->post("tarih");
			$tarih2 = $this->input->post("tarih2");
			
			$this->load->model('db_islem');
			
			
			$veri = array (   "marka_id" => $marka, "urun_id"=> $urun,  "musteri_id"=>$musteri, "gonderim"=>$gonderim, "not_"=>$not_ , "ucret"=>$ucret, "durum"=> $durum, "tarih"=> $tarih,  "bitis"=> $tarih2, "user_id"=>$user_id );
			if(!$this->bos_varmi(array($urun,$musteri,$tarih)))
			{
				
				if($this->input->get("m"))
				{
					$data[ "sonuc" ]  	 = "Tamir Gönderim kaydınız başarı ile düzenlendi. "; 
					$ekleme 		       		 =  $this->db_islem->duzenle("tamir", $this->input->get("m"), $veri);
				}else{
					$data[ "sonuc" ]  = "Tamir Gönderim kaydınız başarı ile oluşturuldu. "; 
					$ekleme 						= $this->db_islem->ekle("tamir", $veri, "hepsi");
				}
				
			}else{
				$data[ "sonuc" ]  = "Lütfen Boş Alan Bırakmayınız.."; 
			} 
			
		}// Post sonu
		
	
		
		$this->load->view('header', $data);
		$this->load->view('sol');
		$this->load->view('ust');
		$this->load->view('tamire_gonder');
		$this->load->view('footer');
		
	}
	
	public function musteri_duzen()
	{
		$this->session_control();
		$data["musteriler"] = $this->db->get_where("musteriler", array("aktif"=>0)); 
		$data["bos"] = $this->session->userdata('user_id');
		
		if($this->input->get("id"))
		{
			$data["sonuc"] = $this->sil2($this->input->get("id"), "musteriler"); 
			$adres = base_url()."musteri_duzen?sonuc=".$data["sonuc"]; 
			header("Location: $adres");
		}
		if($this->input->get("sonuc"))
			$data["sonuc"] = $this->input->get("sonuc"); 
		
		$this->load->view('header', $data);
		$this->load->view('sol');
		$this->load->view('ust');
		$this->load->view('musteri_duzen');
		$this->load->view('footer');
		
	}
	public function firma_duzen()
	{
		$this->session_control();
		$data["firmalar"] = $this->db->get("firmalar"); 
		$data["bos"] = $this->session->userdata('user_id');
		
		if($this->input->get("id"))
		{
			if($this->db->get_where("urunler", array("firma"=>$this->input->get("id"), "aktif"=>0))->result())
			{
					$data["sonuc"] ="Bu Marka üzerine kayıtlı ürünler varken markayı silemezsiniz. Önce Ürünleri silmelisiniz. İsterseniz marka adını değiştirebilirsiniz. "; 
					$adres = base_url()."firma_duzen?sonuc=".$data["sonuc"]; 
				header("Location: $adres");
			}else{
				$data["sonuc"] = $this->sil2($this->input->get("id"), "firmalar"); 
				$adres = base_url()."firma_duzen?sonuc=".$data["sonuc"]; 
				header("Location: $adres");
			}
			
		}
		if($this->input->get("sonuc"))
			$data["sonuc"] = $this->input->get("sonuc"); 
		
		$this->load->view('header', $data);
		$this->load->view('sol');
		$this->load->view('ust');
		$this->load->view('firma_duzen');
		$this->load->view('footer');
		
	}
	public function urun_duzen()
	{ 
		$this->session_control();
	
		$data["urunler"] = $this->db->get_where("urunler",array("aktif"=>0)); 
		$data["bos"] = $this->session->userdata('user_id');
		
		if($this->input->get("id"))
		{
			$stk  = $this->stokta_varmi($this->input->get("id"));
			if($stk<1)
			{
				$data["sonuc"] = $this->sil2($this->input->get("id"), "urunler"); 
				$adres = base_url()."urun_duzen?sonuc=".$data["sonuc"]; 
				header("Location: $adres");
			}else{
				$data["sonuc"] ="Stokta ürün varken silme işlemi yapamazsınız. Önce stoklarınızı eritmelisiniz.";
				$adres = base_url()."urun_duzen?sonuc=".$data["sonuc"]; 
				header("Location: $adres");
			}
			
		}
		if($this->input->get("sonuc"))
			$data["sonuc"] = $this->input->get("sonuc"); 
		
		$this->load->view('header', $data);
		$this->load->view('sol');
		$this->load->view('ust');
		$this->load->view('urun_duzen');
		$this->load->view('footer');
		
	}
	public function stokta_varmi($id)
	{
		$toplam_stok = 0; 
		$stoklar = $this->db->get_where("stoklar", array("u_id"=>$id));
		foreach($stoklar->result() as $rw)
		{
			$toplam_stok += $rw->adet;
		}			
		$satislar = $this->db->get_where("satislar", array("urun"=>$id, "user_id"=>$this->session->userdata('user_id')));
		foreach($satislar->result() as $rw)
		{
			$toplam_stok -= $rw->adet;
		}		
		return $toplam_stok;
	}
	
	public function stok_duzen()
	{ 
		$this->session_control();
	$data["bos"] = $this->session->userdata('user_id');
		$data["stoklar"] = $this->db->get("stoklar"); 
		
		
		if($this->input->get("id"))
		{
			$data["sonuc"] = $this->sil($this->input->get("id"), "stoklar"); 
			$adres = base_url()."stok_duzen?sonuc=".$data["sonuc"]; 
			header("Location: $adres");
		}
		if($this->input->get("sonuc"))
			$data["sonuc"] = $this->input->get("sonuc"); 
		
		$this->load->view('header', $data);
		$this->load->view('sol');
		$this->load->view('ust');
		$this->load->view('stok_hareketleri');
		$this->load->view('footer');
		
	}
	public function tamir_duzen()
	{ 
		$this->session_control();
	
		$data["tamir"] = $this->db->get("tamir"); 
		$data["bos"] = $this->session->userdata('user_id');
		
		if($this->input->get("id"))
		{
			$data["sonuc"] = $this->sil($this->input->get("id"), "tamir"); 
			$adres = base_url()."tamir_duzen?sonuc=".$data["sonuc"]; 
			header("Location: $adres");
		}
		
		if($this->input->get("sonuc"))
			$data["sonuc"] = $this->input->get("sonuc"); 
		
		$this->load->view('header', $data);
		$this->load->view('sol');
		$this->load->view('ust');
		$this->load->view('tamir_duzen');
		$this->load->view('footer');
		
	}
	
	public function satislar()
	{
		$this->session_control();
		
		
		$carr = $this->db->get("satislar"); 
		$data["satislar"] =  array_reverse($this->tarih_sira($carr));
		
		$data["bos"] = $this->session->userdata('user_id');
		
		if($this->input->get("id"))
		{
			$urn = $this->db->get_where("urunler", array("user_id"=>$data["bos"], "id"=>$this->input->get("id")));
			if($urn->result())
			{
				$u_res = $urn->result();
				$u_ = $u_res[0];
				$ade = $u_->adet;
				$satilan = $u_->satilan; 
				$barkod =  $u_->barkod;
			
				$sts = $this->db->get_where("satislar", array("user_id"=>$data["bos"], "barkod"=>$barkod));
				$s_res = $sts->result();
				$s_ = $s_res[0];
				$adet = $s_->adet;
				
				$yeni = $adet + $ade;
				$yeni_sat =  $adet - $satilan;
				$this->load->model('db_islem');
				$veri_ = array ("adet"=> $yeni, "satilan" => $yeni_sat);
				$duzenleme 	= $this->db_islem->duzenle("urunler", $u_->id, $veri_);
			}
			$data["sonuc"] = $this->sil($this->input->get("id"), "satislar"); 
			$adres = base_url()."satislar?sonuc=".$data["sonuc"]; 
			header("Location: $adres");
		}
		if($this->input->get("sonuc"))
			$data["sonuc"] = $this->input->get("sonuc"); 
		
		
		$this->load->view('header', $data);
		$this->load->view('sol');
		$this->load->view('ust');
		$this->load->view('satislar');
		$this->load->view('footer');
		
	}
	public function satis()
	{
		$this->session_control();
		
		$data["urunler"] = $this->db->get("urunler");
		$data["subeler"] = $this->db->get("subeler");
		$data["musteriler"] = $this->db->get("musteriler");
		$data["bos"] = $this->session->userdata('user_id');
		$borc_bakiyesi = false;
		
		if($this->input->get("m"))
		{
			$id =$this->input->get("m") ;
			$kontrol = $this->db->get_where("satislar", array('id' => $id));
			if($kontrol->result())
			{
				$ko = $kontrol->result();
				$k = $ko[0];
				$data["veriler"] = $k; 
			}
		}
		
		if( $this->input->post("barkod") || $this->input->post("urun") )
		{
			
			$barkod = $this->input->post("barkod");
			$urun = $this->input->post("urun");
			$seri = $this->input->post("seri");
			$sol_seri = $this->input->post("sol_seri");
			$adet = $this->input->post("adet");
			$satis =$this->int_tutar( $this->input->post("satis"));
			$tahsil = $this->int_tutar( $this->input->post("tahsil"));//// tahsil edileni muhasebeye entegre et.. 
			$musteri = $this->input->post("musteri");
			$sube = $this->input->post("sube");
			$tarih = $this->input->post("tarih");
			
			$this->load->model('db_islem');
			$veri = array (  "barkod" => $barkod, "urun" => $urun, "adet"=> $adet, "satis"=>$satis, "tahsil"=>$tahsil, "musteri"=>$musteri, "tarih"=> $tarih, "user_id"=>$data["bos"], "seri"=>$seri, "sol_seri"=>$sol_seri, "sube_id"=>$sube  );
					/////////////////////////////////////////////////
			if($this->baglimi)
			{
				$borc_  = $satis - $tahsil;
				if($borc_ >0 )
					$borc_bakiyesi =true; 
				
				$hes_id = $this->db->get_where("musteriler", array("id"=>$musteri))->result()[0]->baglanti_id;
				if($hes_id == 0 )
				{
					$this->yeni_hesap_olustur($musteri); 
					$hes_id = $this->db->get_where("musteriler", array("id"=>$musteri))->result()[0]->baglanti_id;
				}
				$ur = $this->db->get_where("urunler", array("id"=>$urun))->result()[0]->urun;
				$veri_ = array (  "hesap_id"=>$hes_id, "tip_id"=>1, "aciklama"=>$adet." adet ".$ur." ürünü satılmıştır.", "gelir"=>$tahsil, "alacak"=>$tahsil, "tarih"=>$tarih, "user_id"=>$data["bos"] );
				$veri_2= array (  "hesap_id"=>$hes_id, "tip_id"=>1, "aciklama"=>$adet." adet ".$ur." ürünü satılıp tahsilatından geriye kalan bakiye.", "borc"=>$borc_, "tarih"=>$tarih, "user_id"=>$data["bos"] );
			} 
			/////////////////////////////////////////////////
			
			
			if($this->input->get("m"))
			{
					$id =$this->input->get("m") ;
					$duzenleme 	= $this->db_islem->duzenle("satislar", $id, $veri);			
					$data[ "sonuc" ]  = "Satış kaydınız başarı ile Düzenlendi. "; 
			}else{
					/////////////////////////////////////////////////
				if($this->baglimi)
				{
					
					$id1=  $this->db_islem->ekle("satislar", $veri, 1);
					$id2= $this->db_islem->ekle("cari", $veri_, 1);
					if($borc_bakiyesi)
						$id3= $this->db_islem->ekle("cari", $veri_2, 1);
					
					$veri["baglanti_id"] = $id2;
					$veri_["baglantili_id"] = $id1;
					$veri_2["baglantili_id"] = $id1;
					
					$e3 = $this->db_islem->duzenle("satislar",$id1, $veri);
					$e4 = $this->db_islem->duzenle("cari",$id2, $veri_);
					if($borc_bakiyesi)
						$e5 = $this->db_islem->duzenle("cari",$id3, $veri_2);
					
				}else{
					$ekleme = $this->db_islem->ekle("satislar", $veri, "hepsi");
				}
				/////////////////////////////////////////////////
				//$ekleme 	= $this->db_islem->ekle("satislar", $veri, "hepsi");
				$data[ "sonuc" ]  = "Satış kaydınız başarı ile oluşturuldu. "; 
			}
		
		}
		
		
		
		$this->load->view('header', $data);
		$this->load->view('sol');
		$this->load->view('ust');
		$this->load->view('satis');
		$this->load->view('footer');
		
	}
	
	public function aylik_takip()
	{
		$this->session_control();
		
		if(!$this->input->get("sube"))
		{
			$carr = $this->db->get("satislar"); 
		}else{
			$carr= $this->db->get_where("satislar",array("sube_id"=>$this->input->get("sube")));
			
		}
			
		$sir 						=  array_reverse($this->tarih_sira($carr));
		if($this->input->get("ay"))
		{
			$ay= $this->input->get("ay"); 
			$ara = array();
			foreach($sir as $row)
			{
				$yil = date("Y");
				
				if($ay =="Ocak")
					$ay ="01";
				
				if($ay =="Şubat")
					$ay ="02";
				
				if($ay =="Mart")
					$ay ="03";
				
				if($ay =="Nisan")
					$ay ="04";
				
				if($ay =="Mayıs")
					$ay ="05";
				
				if($ay =="Haziran")
					$ay ="06";
				
				if($ay =="Temmuz")
					$ay ="07";
				
				if($ay =="Ağustos")
					$ay ="08";
				
				if($ay =="Eylül")
					$ay ="09";
				
				if($ay =="Ekim")
					$ay ="10";
				
				if($ay =="Kasım")
					$ay ="11";
				
				if($ay =="Aralık")
					$ay ="12";
				
				$parc = explode("/", $row->tarih);
				if($parc[1] == $ay && $parc[2] == $yil)
					array_push($ara, $row);
				
					
			}
			$sir= $ara; 
		}
		
		$data["satislar"]	= $sir;
		
		
		
		$data["subeler"] = $this->db->get("subeler");
		$data["bos"] = $this->session->userdata('user_id');
		
			
		$this->load->view('header', $data);
		$this->load->view('sol');
		$this->load->view('ust');
		$this->load->view('aylik');
		$this->load->view('footer');
		
	}
		/*   --------------------------   - -       Kontrol Fonksiyonları   --   --------------------------------------------- */
	
	public function tarih_sira($caris)
	{
			$user_id = $this->session->userdata('user_id');
			$baslangic = $this->input->get("baslangic");
			$bitis = $this->input->get("bitis");
			
			if($this->input->get("baslangic") =="" ||  !$this->input->get("baslangic"))
				$baslangic = "01/01/".date('Y');
			  
			if($this->input->get("bitis") =="" || !$this->input->get("bitis") ) 
				$bitis = "31/12/".date('Y');
			
			//echo $baslangic." tarihinden ".$bitis."tarihine kadar seçildi";
			
			$zaman_ar = $this->tarihbul($baslangic,$bitis);
			
		
			$yeni_caris = array();
			foreach($zaman_ar as $rw)
			{
				foreach ($caris->result() as $row)
				{
					if ($row->tarih==$rw)
					{ /// Bu aralıktaysa ekle
						array_push($yeni_caris, $row);
					}
				}
				
			}
		
			//// buraya tarih sıralama fonksiyonu ekleyip return array olmalı 
			return  $yeni_caris;
	}
	public function fazla()
	{
		
		$user= $this->session->userdata('user_id');
		$hesaplar = $this->db->get("hesaplar");
		foreach($hesaplar->result() as $row)
		{
			$hes = $this->db->get_where("hesaplar", array("user_id"=> $user , "hesap" =>$row->hesap ));
			$c = $hes->num_rows();
			if($c >1)
			{
				foreach($hes->result() as $rw)
				{
					echo "--------------".$rw->hesap."/".$rw->baglanti_id;
					if($rw->baglanti_id ==0)
						$this->db->delete('hesaplar', array('id' => $rw->id));
				}
				echo "<br />";
			}
			
			
		}
		
	}
	public function sil($id, $vt)
	{
				$sonuc = "Bu işlemi yapmaya yetkiniz bulunmamaktadır. ";
				$user = $this->session->userdata('user_id');
				$vr = $this->db->get_where($vt, array("id"=>$id, "user_id" =>$user));
				if($vr->result())
				{	
					$this->load->model('db_islem');	
					$silme = $this->db_islem->sil($vt,$id);
					$sonuc = "Silme işlemi başarıyla gerçekleşti. ";
					return $sonuc; 
				}else{
					return $sonuc; 
				}
	}
	public function sil2($id, $vt)
	{
				$sonuc = "Bu işlemi yapmaya yetkiniz bulunmamaktadır. ";
				$user = $this->session->userdata('user_id');
				$vr = $this->db->get_where($vt, array("id"=>$id, "user_id" =>$user));
				$arr  = array("aktif"=>1);
				if($vr->result())
				{	
					$this->load->model('db_islem');	
					$silme = $this->db_islem->duzenle($vt,$id,$arr);
					$sonuc = "Silme işlemi başarıyla gerçekleşti. ";
					return $sonuc; 
				}else{
					return $sonuc; 
				}
	}
	
	public function int_tutar($tutar)
	{
			$ara = array(".","₺"," ");
			$tutar = str_replace($ara,"", $tutar);
			$tutar = str_replace(",",".", $tutar);
			return $tutar; 
	}
	public function bos_varmi($arr)
	{
		$kont =false;
		foreach($arr as $a)
		{
			if($a =="")
				$kont =true;
		}
		
		return $kont; 
	}
	
	public function ilk_user_kurulum()
	{
		$this->hesler(array("tip_id"=>1, "hesap"=>"Kasa", "user_id"=>$this->session->userdata('user_id')));
		$this->hesler(array("tip_id"=>2, "hesap"=>"Banka", "user_id"=>$this->session->userdata('user_id')));
		$this->hesler(array("tip_id"=>3, "hesap"=>"Alınan Çekler", "user_id"=>$this->session->userdata('user_id')));
		$this->hesler(array("tip_id"=>4, "hesap"=>"Alınan Senetler", "user_id"=>$this->session->userdata('user_id')));
		$this->hesler(array("tip_id"=>5, "hesap"=>"Yazılan Çekler", "user_id"=>$this->session->userdata('user_id')));
		$this->hesler(array("tip_id"=>6, "hesap"=>"Yazılan Senetler", "user_id"=>$this->session->userdata('user_id')));
	}
	
	public function hesler($veri)
	{
		$this->load->model('db_islem');
		$ekleme 	= $this->db_islem->ekle("hesaplar", $veri, "hepsi");
	}
	
	public function yeni_hesap_olustur($id)
	{
		
		$musteri = $this->db->get_where("musteriler", array("id"=>$id))->result()[0];
		$user_id = $this->session->userdata('user_id');
		
		$hesap_veri = array (  "tip_id"=>10, "hesap" => $musteri->isim, "tel1"=> $musteri->tel, "tel2"=> $musteri->tel2, "mail"=>$musteri->mail, "adres"=>$musteri->adres,  "user_id"=>$user_id, "baglanti_id"=>$id );
		$this->load->model('db_islem');
		$ekle = $this->db_islem->ekle("hesaplar", $hesap_veri, 1);
		$ver = array("baglanti_id"=> $ekle);
		$duz = $this->db_islem->duzenle("musteriler", $id, $ver);
		
	}
	
	public function tarihbul($baslangic,$bitis) { 
		$kes1=explode('/',$baslangic); 
		$kes2=explode('/',$bitis); 
		$time1=mktime(0,0,0,$kes1[1],$kes1[0],$kes1[2]); 
		$time2=mktime(0,0,0,$kes2[1],$kes2[0],$kes2[2]); 
		$arr =array();
		while($time1<=$time2) { 
			$x=date('d/m/Y', ($time1)); 
			array_push($arr, $x);
			$time1=$time1+86400; 
		} 
		return $arr;
	}  
	
	
	public function tarih_siralamasi($ar)
	{
		
		$dolu_ar = $ar;
		$bos_ar = array();
		while(count($dolu_ar) >0)
		{
			$i=0;
			foreach ($ar as $row)
			{
				$parca = explode("/", $row->tarih);
				$gun = $parca[0];
				$ay = $parca[1];
				$yil = $parca[2];
				$ekle =false ;
				 
				foreach($dolu_ar as $rw)
				{
					$parca_ = explode("/", $rw->tarih);
					$gun_ = $parca_[0];
					$ay_= $parca_[1];
					$yil_ = $parca_[2];
					if($yil<= $yil_ && $ay <=$ay_ && $gun <= $gun_)
						$ekle =true;
					
				}
				
				if($ekle)
				{
					array_push($bos_ar, $row);
					$sil_id =0 ; 
					$silinecek = 0;
					foreach($dolu_ar as $rw)
					{
						if($row->id == $rw->id )
							$silinecek = $sil_id;
						
						$sil_id++;	
					}
					 array_splice($dolu_ar, $silinecek, 1);
				}
				
				$i++;
			}
		}
		return $bos_ar ;
	}
	/*   --------------------------   - -       Kullanıcı İşlemleri   --   --------------------------------------------- */	
	
	public function talep_formu(){
		$kime = "info@optiksatis.com";
		$konu = $this->input->post("isim_soyisim"). " - Talep Formu";
		$mesaj = "\n \n \n  Kimden : " .$this->input->post("isim_soyisim"). " - " .$this->input->post("eposta"). "\n \n \n  Paket : " .$this->input->post("paket"). "\n \n \n  Mesaj : ".$this->input->post("mesaj"). "";
		$gonder=$this->ma_il($kime, $konu, $mesaj);	
		if($gonder)
			echo "<script>alert('Fikriniz ve bilgileriniz  gönderildi.En yakın zamanda dönüş yapılacaktır')</script>";
		else
			echo "<script>alert('Bir sorun oluştu,mail fonksiyonu çalışmadı!')</script>";
		
		echo '<meta http-equiv="refresh" content="0;URL=http://www.optiksatis.com/">';
	}
	
	public function ma_il($kime, $konu, $mesaj){
		
		$mail_adresiniz	= "info@optiksatis.com";
		$mail_sifreniz	= "445566aa";
		$gidecek_adres	=  $kime;
		$domain_adresi	= "optiksatis.com";	//www olmadan yazınız
		///////////////////////////////////////////////////////////
		///////////////////////////////////////////////////////////
		require("include/class.php");
		@$mail = new PHPMail();
		@$mail->Host       = "mail.".$domain_adresi;
		@$mail->SMTPAuth   = true;
		@$mail->Username   = $mail_adresiniz;
		@$mail->Password   = $mail_sifreniz;
		@$mail->IsSMTP();
		@$mail->AddAddress($gidecek_adres);
		@$mail->From       = $mail_adresiniz;
		@$mail->FromName   = $mail_adresiniz;
		@$mail->Subject    = $konu;
		@$mail->Body       = $mesaj;
		@$mail->AltBody    = "";
		if(@$mail->Send()){
			return true;
		}			
	}
	
	public function iller() 
	{
		$bas = "01/01/2017";
		$bit= "01/06/2017";
		$this->tarihbul($bas,$bit);
		
	}
	
	
	public function sifre_degistir()
	{
		
		$this->session_control();
		$this->load->model("users");
		$this->load->model('db_islem');
		$user_id = $this->session->userdata('user_id');
		
		if( $this->input->post("e_sifre") )
		{
			$e_sifre = $this->input->post("e_sifre");
			$sifre_1 = $this->input->post("sifre_1");
			$sifre_2 = $this->input->post("sifre_2");						
			
			if ($sifre_1==$sifre_2){
				if($this->users->login('users','id','sifre',$user_id,$e_sifre))
				{
					$data["bilgi"] = "Şifreniz Değiştirildi.";
					$data["alert"] = "success";
					$veri = array("sifre" => $sifre_1);
					$guncelle = $this->db_islem->duzenle("users",$user_id, $veri);
				}
				else
				{
					$data["bilgi"] = "Eski şifrenizi yanlış girdiniz.";
					$data["alert"] = "danger";
				}			
			}else{
				$data["bilgi"] = "Yeni şifreler uyuşmuyor.";
				$data["alert"] = "danger";
			}
		}
		$this->load->view('header', @$data);
		$this->load->view('sol');
		$this->load->view('ust');
		$this->load->view('sifre_degistir');
		$this->load->view('footer');
	}
	
	
	public function kayit()
	{
		if( $this->input->post("k_adi") )
		{
			$k_adi = $this->input->post("k_adi");
			$mail = $this->input->post("mail");
			$tel = $this->input->post("tel");
			$sifre = $this->input->post("sifre");
			$this->load->model('db_islem');
			
			$kontrol = $this->db->get_where("users", array('mail' => $mail));
			if($kontrol->result())
			{
			
					$data[ "sonuc" ]  = "Bu mail ile daha önce kayıt oluşturulmuştur. Şifrenizi unuttuysanız lütfen şifremi unuttum linkini tıklayarak mail adresinize şifrenizin gönderimini sağlayınız.  "; 
			}else{
				
				$data[ "sonuc" ]  = "İşyeri kaydınız başarı ile oluşturuldu. Lütfen giriş yapınız. "; 
				$veri = array ( "mail" => $mail, "k_adi" => $k_adi, "sifre"=> $sifre, "tel"=>$tel );
				$ekleme 						= $this->db_islem->ekle("users", $veri, "hepsi");
			}
			$this->load->view('giris' , $data);
		}else{
			redirect("welcome");
		}
	}
	
	public function sifre_yenile()
	{
		if( $this->input->post("mail") )
		{
			$mail = $this->input->post("mail");
			$this->load->model('db_islem');
			$rand_sifre = rand(23548568,98654822);
			$kontrol = $this->db->get_where("users", array('mail' => $mail));
			if($kontrol->result())
			{
				foreach($kontrol->result() as $row)
					$id = $row->id;
				$data[ "sonuc" ]  = "Yeni şifreniz mail olarak gönderilmiştir."; 
				$veri = array ("sifre"=> $rand_sifre);
				$ekleme = $this->db_islem->duzenle("users",$id, $veri);
				$kime = $mail;
				$konu = "Şifre Yenileme";
				$mesaj = "\n \n \n Yeni Şifreniz : ". $rand_sifre;
				$gonder=$this->ma_il($kime, $konu, $mesaj); 
				if($gonder)
					$data[ "sonuc" ]  = "Yeni şifreniz mail olarak gönderilmiştir."; 
				else
					$data[ "sonuc" ]  = "Yeni şifreniz gönderilirken bir sorun oluştu."; 
			}else{
				$data[ "sonuc" ]  = "Kayıtlı mail adresi bulunamadı!"; 
			}
			$this->load->view('giris' , $data);
		}else{
			redirect("welcome");
		}
	}
	
	public function giris()
	{
			$this->load->view('giris');
	}
		public function cikis()
	{
		$this->session->unset_userdata('mail');
		$this->session->unset_userdata('user_id');
		$this->session->unset_userdata('login');
		$this->session->sess_destroy();
		redirect("welcome");
	}
	
	
	/* ------------------------------------------------------  Kullanıcı GİRİŞİ ------------------------------------------- */
	
	public function login_control()
	{
            
		$this->load->library("form_validation");
		$this->form_validation->set_rules('email', 'Email', 'required|valid_email|trim|callback_username_check');
		$this->form_validation->set_rules('password', 'Password', 'required');
		if($this->form_validation->run())
		{
			$email=$this->input->post("email");
			$id_al1=$this->db->get_where("users", array('mail' => $email));
			$id_al2=$id_al1->result();
			$id_al=$id_al2[0]->id;
			
			
			$veri = array(
				'mail' => $this->input->post("email"),
				'user_id' => $id_al,
				'login' => 1
			);
			
			$this->session->set_userdata('email',$email);
			$this->session->set_userdata($veri);
			redirect('welcome');
		}else{
			$this->session->set_userdata('bilgi','E-Posta adresinizi yada şifresinizi yanlış girdiniz!');
			$this->session->set_userdata('alert','danger');
			redirect('welcome/giris');			
		}	
	}
	public function username_check()
	{
		$this->load->model("users");
		$email = $this->input->post('email');
		$password = $this->input->post("password");	
		if($this->users->login('users','mail','sifre',$email,$password))
		{
			return true;
		}else{
			return false;
		}	
	}
	public function session_control()
	{
		if(!$this->session->userdata("login"))
		{
			redirect('welcome/giris');
		}
	}
	/* ------------------------------------------------------  Kullanıcı GİRİŞİ Sonu ------------------------------------ */
	
	
	
}
/*  Silinenler
public function alacak_ekle()
	{
		$this->session_control();
		
		$data["bos"] = $this->session->userdata('user_id');
		$user_id = $this->session->userdata('user_id');
		
		if( $this->input->post("alacaklar") )
		{
			$aciklama = $this->input->post("aciklama");
			$tarih = $this->input->post("tarih");
			$vade_tarihi = $this->input->post("vade_tarihi");
			$ucret = $this->input->post("ucret");
			$sekil = $this->input->post("sekil");
			$sekil_2 = $this->input->post("sekil_2");
			
			if(empty($sekil))
				$sekil = $sekil_2;
			
			$this->load->model('db_islem');
			$veri = array ( "aciklama" => $aciklama, "tarih" => $tarih, "vade_tarihi" => $vade_tarihi, "ucret"=> $ucret, "sekil"=>$sekil, "user_id"=>$user_id );
			
			if($this->input->get("m"))
			{
				$id =$this->input->get("m") ;
				$kontrol = $this->db->get_where("alacaklar", array('user_id' => $user_id, 'id' => $id));
			
				if($kontrol->result())
				{
						$data[ "sonuc" ]  = "Alacak kaydınız başarı ile güncellenmiştir. "; 
						$ekleme = $this->db_islem->duzenle("alacaklar",$id, $veri);
				}else{
						$data[ "sonuc" ]  = "Hata !! Lütfen öncelikle bilgilerini düzenlemek istediğiniz alacak seçiniz."; 
				}
				
			}else{
				$data[ "sonuc" ]  = "Alacak kaydınız başarı ile oluşturuldu. "; 
				$ekleme = $this->db_islem->ekle("alacaklar", $veri, "hepsi");
			}//Ekleme Sonu	
		}// Post sonu
		
		if($this->input->get("m"))
		{
			$id =$this->input->get("m") ;
			$kontrol = $this->db->get_where("alacaklar", array('user_id' => $user_id, 'id' => $id));
			if($kontrol->result())
			{
				$ko = $kontrol->result();
				$k = $ko[0];
				$data["veriler"] = $k; 
			}
		}
		
		$this->load->view('header', $data);
		$this->load->view('sol');
		$this->load->view('ust');
		$this->load->view('alacak_ekle');
		$this->load->view('footer');
		
	}
	
	public function borc_ekle()
	{
		$this->session_control();
		
		$data["bos"] = $this->session->userdata('user_id');
		$user_id = $this->session->userdata('user_id');
		
		if( $this->input->post("borclar") )
		{
			$aciklama = $this->input->post("aciklama");
			$tarih = $this->input->post("tarih");
			$vade_tarihi = $this->input->post("vade_tarihi");
			$ucret = $this->input->post("ucret");
			$sekil = $this->input->post("sekil");
			$sekil_2 = $this->input->post("sekil_2");
			
			if(empty($sekil))
				$sekil = $sekil_2;
			
			$this->load->model('db_islem');
			$veri = array ( "aciklama" => $aciklama, "tarih" => $tarih, "vade_tarihi" => $vade_tarihi, "ucret"=> $ucret, "sekil"=>$sekil, "user_id"=>$user_id );
			
			if($this->input->get("m"))
			{
				$id =$this->input->get("m") ;
				$kontrol = $this->db->get_where("borclar", array('user_id' => $user_id, 'id' => $id));
			
				if($kontrol->result())
				{
						$data[ "sonuc" ]  = "Borç kaydınız başarı ile güncellenmiştir. "; 
						$ekleme = $this->db_islem->duzenle("borclar",$id, $veri);
				}else{
						$data[ "sonuc" ]  = "Hata !! Lütfen öncelikle bilgilerini düzenlemek istediğiniz borç seçiniz."; 
				}
				
			}else{
				$data[ "sonuc" ]  = "Borç kaydınız başarı ile oluşturuldu. "; 
				$ekleme = $this->db_islem->ekle("borclar", $veri, "hepsi");
			}//Ekleme Sonu	
		}// Post sonu
		
		if($this->input->get("m"))
		{
			$id =$this->input->get("m") ;
			$kontrol = $this->db->get_where("borclar", array('user_id' => $user_id, 'id' => $id));
			if($kontrol->result())
			{
				$ko = $kontrol->result();
				$k = $ko[0];
				$data["veriler"] = $k; 
			}
		}
		
		$this->load->view('header', $data);
		$this->load->view('sol');
		$this->load->view('ust');
		$this->load->view('borc_ekle');
		$this->load->view('footer');
		
	}
	
	
	public function gelir_gider_ekle()
	{
		$this->session_control();
		
		$data["bos"] = $this->session->userdata('user_id');
		$user_id = $this->session->userdata('user_id');
		$data["gelir_veriler2"]  = $this->db->get("gelir_defteri");
		if( $this->input->post("gelir") )
		{
			$aciklama = $this->input->post("gelir_aciklama");
			$ucret = $this->input->post("gelir_ucret");
			$sekil = $this->input->post("gelir_sekil");
			$tarih = $this->input->post("gelir_tarih");			
			$sekil_2 = $this->input->post("gelir_sekil_2");
			$vade_tarihi = $this->input->post("vade_tarihi");
			if(empty($sekil))
				$sekil = $sekil_2;
			
			$this->load->model('db_islem');
			$veri = array (  "aciklama" => $aciklama, "ucret" => $ucret, "sekil"=> $sekil, "tarih"=>$tarih,"vade_tarihi"=>$vade_tarihi, "user_id"=>$user_id );
			
			if($this->input->get("m"))
			{
				$id =$this->input->get("m") ;
				$kontrol = $this->db->get_where("gelir_defteri", array('user_id' => $user_id, 'id' => $id));
			
				if($kontrol->result())
				{
						$data[ "sonuc" ]  = "Gelir kaydınız başarı ile güncellenmiştir. "; 
						$ekleme = $this->db_islem->duzenle("gelir_defteri",$id, $veri);
				}else{
						$data[ "sonuc" ]  = "Hata !! Lütfen öncelikle bilgilerini düzenlemek istediğiniz geliri seçiniz."; 
				}
				
			}else{
				$data[ "sonuc" ]  = "Gelir kaydınız başarı ile oluşturuldu. "; 
				$ekleme = $this->db_islem->ekle("gelir_defteri", $veri, "hepsi");
				if(($sekil == "Çek") or ($sekil == "Senet") or ($sekil == "Kart"))
					$ekleme = $this->db_islem->ekle("alacaklar", $veri, "hepsi");
			}//Ekleme Sonu	
		}// Post sonu
		
		if($this->input->get("q")=="1")
		{
			$id =$this->input->get("m") ;
			$kontrol = $this->db->get_where("gelir_defteri", array('user_id' => $user_id, 'id' => $id));
			if($kontrol->result())
			{
				$ko = $kontrol->result();
				$k = $ko[0];
				$data["gelir_veriler"] = $k; 
			}
		}
		
		if( $this->input->post("gider") )
		{
			$aciklama = $this->input->post("gider_aciklama");
			$ucret = $this->input->post("gider_ucret");
			$sekil = $this->input->post("gider_sekil");
			$tarih = $this->input->post("gider_tarih");	
			$sekil_2 = $this->input->post("gider_sekil_2");
			$vade_tarihi = $this->input->post("vade_tarihi");
			$cek = $this->input->post("cek");
			$senet = $this->input->post("senet");
			
			if(empty($sekil))
				$sekil = $sekil_2;			
			
			$this->load->model('db_islem');
			$veri = array (  "aciklama" => $aciklama, "ucret" => $ucret, "sekil"=> $sekil, "tarih"=>$tarih, "vade_tarihi"=>$vade_tarihi,"user_id"=>$user_id );
			
				if(!empty($cek)){
					$ck = $this->db->get_where("gelir_defteri", array("user_id"=>$data["bos"], "id"=>$cek));
					$c_res = $ck->result();
					$c_ = $c_res[0];
					$ucret = $c_->ucret;
					$sekil = "Çek";
					$gelir_cek_id = $c_->id;
					$veri = array (  "aciklama" => $aciklama, "ucret" => $ucret, "sekil"=> $sekil, "tarih"=>$tarih, "vade_tarihi"=>$vade_tarihi,"user_id"=>$user_id );		
				}				
				if(!empty($senet)){
					$ck = $this->db->get_where("gelir_defteri", array("user_id"=>$data["bos"], "id"=>$senet));
					$c_res = $ck->result();
					$c_ = $c_res[0];
					$ucret = $c_->ucret;
					$sekil = "Senet";
					$gelir_cek_id = $c_->id;
					$veri = array (  "aciklama" => $aciklama, "ucret" => $ucret, "sekil"=> $sekil, "tarih"=>$tarih, "vade_tarihi"=>$vade_tarihi,"user_id"=>$user_id );
				}
			
			if($this->input->get("q")=="0")
			{
				$id =$this->input->get("m") ;
				$kontrol = $this->db->get_where("gider_defteri", array('user_id' => $user_id, 'id' => $id));
			
				if($kontrol->result())
				{
						$data[ "sonuc" ]  = "Gider kaydınız başarı ile güncellenmiştir. "; 
						$ekleme = $this->db_islem->duzenle("gider_defteri",$id, $veri);
				}else{
						$data[ "sonuc" ]  = "Hata !! Lütfen öncelikle bilgilerini düzenlemek istediğiniz gideri seçiniz.";
				}
				
			}else{
				$data[ "sonuc" ]  = "Gider kaydınız başarı ile oluşturuldu.";
				$ekleme = $this->db_islem->ekle("gider_defteri", $veri, "hepsi");
				if((empty($cek)) and (empty($cek)))
					$ekleme = $this->db_islem->ekle("borclar", $veri, "hepsi");				
				if(!empty($cek))
					$ekleme = $this->sil($gelir_cek_id, "gelir_defteri");
				if(!empty($senet))
					$ekleme = $this->sil($gelir_cek_id, "gelir_defteri");
			}//Ekleme Sonu	
		}// Post sonu
		
		if($this->input->get("q")=="0")
		{
			$id =$this->input->get("m") ;
			$kontrol = $this->db->get_where("gider_defteri", array('user_id' => $user_id, 'id' => $id));
			if($kontrol->result())
			{
				$ko = $kontrol->result();
				$k = $ko[0];
				$data["gider_veriler"] = $k; 
			}
		}
		
		$this->load->view('header', $data);
		$this->load->view('sol');
		$this->load->view('ust');
		$this->load->view('gelir_gider_ekle');
		$this->load->view('footer');
		
	}
	
 */
