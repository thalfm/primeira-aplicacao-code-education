<?php
/**
 * Created by PhpStorm.
 * User: thales
 * Date: 01/05/2018
 * Time: 14:27
 */

namespace App\Application\Action\Campaign;


use App\Application\Form\CampaignForm;
use App\Application\Form\HttpMethodElement;
use App\Domain\Entity\Campaign;
use App\Domain\Persistence\CampaignRepositoryInterface;
use App\Domain\Persistence\TagRepositoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Expressive\Flash\FlashMessageMiddleware;
use Zend\Expressive\Flash\FlashMessagesInterface;
use Zend\Expressive\Router\RouterInterface;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\Form\Form;

abstract class CampaignAbstractAction
{
    const MSG_UPDATE_SUCCESS = 'Campanha atualizada com sucesso!';
    const MSG_CREATE_SUCCESS = 'Campanha cadastrada com sucesso!';
    const MSG_DELETE_SUCCESS = 'Campanha deletada com sucesso!';

    /**
     * @var TemplateRendererInterface
     */
    private $template;
    /**
     * @var TagRepositoryInterface
     */
    private $repository;
    /**
     * @var RouterInterface
     */
    private $router;
    /**
     * @var Form $form
     */
    private $form;

    public function __construct(
        CampaignRepositoryInterface $repository,
        TemplateRendererInterface $template,
        RouterInterface $router,
        CampaignForm $form
    ){
        $this->template = $template;
        $this->repository = $repository;
        $this->router = $router;
        $this->form = $form;
    }

    /**
     * @return TemplateRendererInterface
     */
    public function getTemplate(): TemplateRendererInterface
    {
        return $this->template;
    }

    /**
     * @return CampaignRepositoryInterface
     */
    public function getRepository(): CampaignRepositoryInterface
    {
        return $this->repository;
    }

    /**
     * @return RouterInterface
     */
    public function getRouter(): RouterInterface
    {
        return $this->router;
    }

    /**
     * @return CampaignForm
     */
    public function getForm(): CampaignForm
    {
        return $this->form;
    }

    /**
     * @param ServerRequestInterface $request
     */
    protected function getEntityBy(ServerRequestInterface $request)
    {
        $id = $request->getAttribute('id');
        $entity = $this->repository->find($id);

        return $entity;
    }

    /**
     * @param Campaign $entity
     * @param HttpMethodElement $element
     */
    protected function bindFormBy(Campaign $entity, HttpMethodElement $element)
    {
        $this->form->add($element);
        $this->form->bind($entity);
    }

    /**
     * @return HtmlResponse
     */
    protected function formResponse(string $template): HtmlResponse
    {
        return new HtmlResponse($this->template->render("app::{$template}", [
            'form' => $this->form
        ]));
    }

    protected function verifyMethod(ServerRequestInterface $request, array $methods): bool
    {
        if (in_array($request->getMethod(), $methods)) {
            return true;
        }

        return false;
    }

    /**
     * @param ServerRequestInterface $request
     * @return Form
     */
    protected function rawDataForm(ServerRequestInterface $request): Form
    {
        $dataRaw = $request->getParsedBody();
        $this->form->setData($dataRaw);

        return $this->form;
    }

    /**
     * @param ServerRequestInterface $request
     * @return bool
     */
    protected function isFormValid(ServerRequestInterface $request): bool
    {
        $dataRaw = $request->getParsedBody();
        $this->form->setData($dataRaw);

        if (!$this->form->isValid()) {
            return false;
        }

        return true;
    }

    /**
     * @param string $uri
     * @return RedirectResponse
     */
    protected function redirectPost(string $uri): RedirectResponse
    {
        $uri = $this->router->generateUri($uri);
        return new RedirectResponse($uri);
    }

    /**
     * @param ServerRequestInterface $request
     * @return FlashMessagesInterface
     */
    protected function messageSuccess(ServerRequestInterface $request, string $message): FlashMessagesInterface
    {
        /** @var FlashMessagesInterface $flashMessage */
        $flashMessage = $request->getAttribute(FlashMessageMiddleware::FLASH_ATTRIBUTE);
        $flashMessage->flash('success', $message);

        return $flashMessage;
    }

    /**
     * @return bool
     */
    abstract protected function formPersiste(Campaign $campaign): bool;
}